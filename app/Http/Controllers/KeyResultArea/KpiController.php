<?php

namespace App\Http\Controllers\KeyResultArea;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\ResponsibleUnit;
use App\Models\StrategicPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KpiController extends Controller
{
    /**
     * "My KPIs" — scoped to the KRA user's own sub_kra_id, never the whole table.
     *
     * Also scoped to a single strategic plan (academic year), since the same
     * KPI code now has one row per plan. Defaults to the Active plan when
     * no academic year is selected.
     */
    public function index(Request $request): Response
    {
        $user = $this->authorizedUser($request);

        $strategicPlans = StrategicPlan::orderByDesc('school_year')
            ->get(['id', 'school_year', 'status']);

        $selectedStrategicPlanId = $this->resolveSelectedStrategicPlan(
            $request,
            $strategicPlans
        );

        $kpis = Kpi::with('responsibleUnits')
            ->withCount('progress')
            ->where('sub_kra_id', $user->sub_kra_id)
            ->where('strategic_plan_id', $selectedStrategicPlanId)
            ->latest()
            ->get();

        return Inertia::render('key-result-area/kpi/index', [
            'kpis' => $kpis,
            'strategicPlans' => $strategicPlans,
            'selectedStrategicPlanId' => $selectedStrategicPlanId,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizedUser($request);

        return Inertia::render('key-result-area/kpi/form', [
            'responsibleUnits' => ResponsibleUnit::orderBy('name')->get(['id', 'name', 'acronym']),
        ]);
    }

    /**
     * Proposing a KPI always lands as Pending against the user's own sub_kra —
     * neither of those fields is accepted from the request.
     */
    /**
     * Proposing a KPI always lands as Pending against the user's own sub_kra
     * and the currently active strategic plan — neither is accepted from
     * the request. KRA users can browse past academic years via the index
     * filter, but new proposals only ever attach to the active one.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $this->authorizedUser($request);

        $activeStrategicPlan = StrategicPlan::where('status', 'Active')->first();

        abort_unless(
            $activeStrategicPlan,
            422,
            'There is no active strategic plan to propose a KPI against.'
        );

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target' => ['nullable', 'string', 'max:255'],
            'unit_of_measure' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'responsible_unit_ids' => ['array'],
            'responsible_unit_ids.*' => ['integer', 'exists:responsible_units,id'],
        ]);

        $responsibleUnitIds = $validated['responsible_unit_ids'] ?? [];
        unset($validated['responsible_unit_ids']);

        $validated['sub_kra_id'] = $user->sub_kra_id;
        $validated['strategic_plan_id'] = $activeStrategicPlan->id;
        $validated['status'] = 'Pending';
        $validated['proposed_by'] = $user->id;

        $kpi = Kpi::create($validated);
        $kpi->responsibleUnits()->sync($responsibleUnitIds);

        return to_route('key-result-area.kpi.index', ['strategic_plan_id' => $activeStrategicPlan->id])
            ->with('success', 'KPI proposed. It is now pending approval.');
    }

    public function edit(Request $request, Kpi $kpi): Response
    {
        $user = $this->authorizedUser($request);

        abort_unless($kpi->sub_kra_id === $user->sub_kra_id, 403);

        $kpi->load('responsibleUnits');

        return Inertia::render('key-result-area/kpi/form', [
            'kpi' => $kpi,
            'responsibleUnits' => ResponsibleUnit::orderBy('name')
                ->get(['id', 'name', 'acronym']),
        ]);
    }


    public function update(Request $request, Kpi $kpi): RedirectResponse
    {
        $user = $this->authorizedUser($request);

        abort_unless($kpi->sub_kra_id === $user->sub_kra_id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target' => ['nullable', 'string', 'max:255'],
            'unit_of_measure' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'responsible_unit_ids' => ['array'],
            'responsible_unit_ids.*' => [
                'integer',
                'exists:responsible_units,id',
            ],
        ]);

        $responsibleUnitIds = $validated['responsible_unit_ids'] ?? [];
        unset($validated['responsible_unit_ids']);

        $kpi->update($validated);
        $kpi->responsibleUnits()->sync($responsibleUnitIds);

        return to_route('key-result-area.kpi.show', $kpi)
            ->with('success', 'KPI updated successfully.');
    }




    public function show(Request $request, Kpi $kpi): Response
    {
        $user = $this->authorizedUser($request);

        abort_unless($kpi->sub_kra_id === $user->sub_kra_id, 403);

        $kpi->load([
            'subKra.kra',
            'responsibleUnits',
            'actionPlans',
            'progress' => fn($query) => $query
                ->orderBy('year')
                ->orderBy('month'),
        ]);

        return Inertia::render('key-result-area/kpi/show', [
            'kpi' => $kpi,
        ]);
    }


    /**
     * Every action here requires the user to actually belong to a sub_kra —
     * an unassigned key_result_area account has nothing to scope queries to.
     */
    protected function authorizedUser(Request $request)
    {
        $user = $request->user();

        abort_unless($user->sub_kra_id, 403, 'Your account is not yet assigned to a Sub KRA. Contact an administrator.');

        return $user;
    }

    /**
     * Resolve which strategic plan (academic year) the index should be
     * scoped to: whatever was requested, if it's a real plan, otherwise
     * the Active plan, otherwise the most recent plan.
     */
    protected function resolveSelectedStrategicPlan(Request $request, $strategicPlans): ?int
    {
        $requested = $request->integer('strategic_plan_id');

        if ($requested && $strategicPlans->contains('id', $requested)) {
            return $requested;
        }

        $active = $strategicPlans->firstWhere('status', 'Active');

        return $active->id ?? $strategicPlans->first()->id ?? null;
    }
}
