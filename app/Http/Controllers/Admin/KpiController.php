<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\ResponsibleUnit;
use App\Models\StrategicPlan;
use App\Models\SubKra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class KpiController extends Controller
{
    public function index(Request $request): Response
    {
        $strategicPlans = StrategicPlan::orderBy('school_year', 'desc')
            ->orderBy('title')
            ->get(['id', 'title', 'school_year', 'status']);

        $selectedStrategicPlanId = $this->resolveSelectedStrategicPlan($request, $strategicPlans);

        $query = Kpi::with([
            'strategicPlan',
            'subKra.kra',
            'responsibleUnits',
        ])->withCount([
            'actionPlans',
            'progress',
        ]);

        $query->where('strategic_plan_id', $selectedStrategicPlanId);

        if ($request->filled('sub_kra_id')) {
            $query->where('sub_kra_id', $request->integer('sub_kra_id'));
        }

        $kpis = $query->get()
            ->sort(function ($a, $b) {
                return version_compare($a->description, $b->description);
            })
            ->values();

        return Inertia::render('admin/kpi/index', [
            'kpis' => $kpis,
            'strategicPlans' => $strategicPlans,
            'subKras' => SubKra::with('kra')
                ->orderBy('code')
                ->get([
                    'id',
                    'kra_id',
                    'code',
                    'title',
                ]),
            'filters' => [
                'sub_kra_id' => $request->input('sub_kra_id'),
                'strategic_plan_id' => (string) $selectedStrategicPlanId,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('admin/kpi/form', [
            'strategicPlans' => StrategicPlan::orderBy('school_year', 'desc')
                ->orderBy('title')
                ->get(['id', 'title', 'school_year']),
            'subKras' => SubKra::with('kra')
                ->orderBy('code')
                ->get(['id', 'kra_id', 'code', 'title']),
            'responsibleUnits' => ResponsibleUnit::orderBy('name')
                ->get(['id', 'name', 'acronym']),
            'selectedSubKraId' => $request->integer('sub_kra_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $responsibleUnitIds = $validated['responsible_unit_ids'] ?? [];
        unset($validated['responsible_unit_ids']);

        $validated['proposed_by'] = $request->user()->id;

        $kpi = Kpi::create($validated);

        $kpi->responsibleUnits()->sync($responsibleUnitIds);

        return to_route('kpi.index', [
            'sub_kra_id' => $kpi->sub_kra_id,
            'strategic_plan_id' => $kpi->strategic_plan_id,
        ])->with('success', 'KPI created.');
    }

    public function show(Kpi $kpi): Response
    {
        $kpi->load([
            'strategicPlan',
            'subKra.kra',
            'responsibleUnits',
            'actionPlans' => fn($q) => $q->orderBy('start_date'),
            'progress' => fn($q) => $q->orderBy('year')->orderBy('month'),
        ]);

        return Inertia::render('admin/kpi/show', [
            'kpi' => $kpi,
        ]);
    }

    public function edit(Kpi $kpi): Response
    {
        $kpi->load([
            'strategicPlan',
            'responsibleUnits',
        ]);

        return Inertia::render('admin/kpi/form', [
            'kpi' => $kpi,
            'strategicPlans' => StrategicPlan::orderBy('school_year', 'desc')
                ->orderBy('title')
                ->get(['id', 'title', 'school_year']),
            'subKras' => SubKra::with('kra')
                ->orderBy('code')
                ->get(['id', 'kra_id', 'code', 'title']),
            'responsibleUnits' => ResponsibleUnit::orderBy('name')
                ->get(['id', 'name', 'acronym']),
        ]);
    }

    public function update(Request $request, Kpi $kpi): RedirectResponse
    {
        $validated = $this->validated($request, $kpi);

        $responsibleUnitIds = $validated['responsible_unit_ids'] ?? [];
        unset($validated['responsible_unit_ids']);

        $kpi->update($validated);

        $kpi->responsibleUnits()->sync($responsibleUnitIds);

        return to_route('kpi.index', [
            'sub_kra_id' => $kpi->sub_kra_id,
            'strategic_plan_id' => $kpi->strategic_plan_id,
        ])->with('success', 'KPI updated.');
    }

    public function destroy(Kpi $kpi): RedirectResponse
    {
        $subKraId = $kpi->sub_kra_id;
        $strategicPlanId = $kpi->strategic_plan_id;

        $kpi->delete();

        return to_route('kpi.index', [
            'sub_kra_id' => $subKraId,
            'strategic_plan_id' => $strategicPlanId,
        ])->with('success', 'KPI deleted.');
    }

    protected function validated(Request $request, ?Kpi $kpi = null): array
    {
        return $request->validate([
            'strategic_plan_id' => [
                'required',
                'integer',
                'exists:strategic_plans,id',
            ],
            'sub_kra_id' => [
                'required',
                'integer',
                'exists:sub_kras,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'target' => [
                'nullable',
                'string',
                'max:255',
            ],
            'unit_of_measure' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                Rule::in([
                    'Pending',
                    'Approved',
                    'Rejected',
                    'Completed',
                ]),
            ],
            'remarks' => [
                'nullable',
                'string',
            ],
            'responsible_unit_ids' => [
                'array',
            ],
            'responsible_unit_ids.*' => [
                'integer',
                'exists:responsible_units,id',
            ],
        ]);
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
