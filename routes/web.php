<?php

use App\Http\Controllers\Admin\InnovativeActionPlanController;
use App\Http\Controllers\Admin\KpiController;
use App\Http\Controllers\Admin\KraController;
use App\Http\Controllers\Admin\StrategicPlanController;
use App\Http\Controllers\Admin\SubKraController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\KeyResultArea\KpiController as KeyResultAreaKpiController;
use App\Http\Controllers\KeyResultArea\ProgressController as KeyResultAreaProgressController;
use App\Http\Controllers\KpiProgressController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResponsibleUnitController;
use App\Http\Controllers\StrategicPlanner\ApprovalController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::inertia('/', 'auth/login')->name('home');
});

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Reports are read-only and shared across admin/strategic_planner/viewer
    // (see menus.tsx). Registered here instead of inside a role group so the
    // single '/reports' URI isn't shadowed by an earlier role-gated route
    // with the same path — see the '/kpi' vs '/kpis' note below.
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('strategic-plans', StrategicPlanController::class)->except(['index', 'show']);
    Route::resource('users', UserController::class);
    Route::resource('kra', KraController::class)->except(['index', 'show']);
    Route::resource('subkra', SubKraController::class)->except(['index', 'show']);
    Route::resource('kpi', KpiController::class)->except(['index', 'show']);

    // Action plans are managed inline from the KPI show page, so only the
    // mutating actions are exposed (no index/create/edit views for this).
    Route::resource('action-plans', InnovativeActionPlanController::class)
        ->only(['store', 'update', 'destroy']);

    // NOTE: progress.store/update/destroy are intentionally NOT re-registered
    // here — they're defined below under a shared group so both admin and
    // key_result_area can hit the same route names (see that group's note).
});

// Admin + Viewer read-only routes
//
// Viewer sees the exact same index/show data as admin, just without any
// mutating actions. Split out from the admin group above (rather than
// registering a viewer-prefixed group like key-result-area does) because
// these resources need no per-user scoping — viewer just needs read access
// to the same list/detail views admin gets. Registering these under the
// admin role group instead would mean the admin group's full resource
// routes always match GET /kpi, /kra, etc. first, shadowing anything
// registered afterward for role:viewer at the same URI.
Route::middleware(['auth', 'role:admin,viewer'])->group(function () {
    Route::resource('strategic-plans', StrategicPlanController::class)->only(['index', 'show']);
    Route::resource('kra', KraController::class)->only(['index', 'show']);
    Route::resource('subkra', SubKraController::class)->only(['index', 'show']);
    Route::resource('kpi', KpiController::class)->only(['index', 'show']);
});

// Strategic Planner Routes
Route::middleware(['auth', 'role:strategic_planner'])->group(function () {
    Route::get('approvals', [ApprovalController::class, 'index'])
        ->name('approvals.index');
    Route::patch('approvals/{kpi}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve');
    Route::patch('approvals/{kpi}/reject', [ApprovalController::class, 'reject'])
        ->name('approvals.reject');
});

// Key Result Area Routes
//
// Prefixed with 'key-result-area/' so this group's 'kpi' resource lands on a
// URI the admin group above doesn't already own — GET /key-result-area/kpi
// vs GET /kpi. Route matching picks the FIRST registered route for a given
// method+URI regardless of name, so without a distinct URI this role's
// requests would always hit the admin route first and 403.
//
// Uses KeyResultArea\KpiController, NOT the Admin one — it scopes every
// query to the signed-in user's own sub_kra_id and renders the
// key-result-area/kpi/* views (no edit/delete controls this role can't use).
Route::middleware(['auth', 'role:key_result_area'])
    ->prefix('key-result-area')
    ->name('key-result-area.')
    ->group(function () {
        Route::resource('kpi', KeyResultAreaKpiController::class)
            ->only([
                'index',
                'show',
                'create',
                'store',
                'edit',
                'update',
            ]);


        Route::get('progress', [KeyResultAreaProgressController::class, 'index'])
            ->name('progress.index');
    });

// Progress mutations (record/edit/delete a monthly entry) are deliberately
// registered OUTSIDE the prefixed/named group above, so the route names stay
// the unprefixed 'progress.store' / 'progress.update' / 'progress.destroy'.
// Both the admin KPI show page and the key_result_area show/progress pages
// use ProgressDialog, which posts to those exact names.
//
// Both roles need access here (admins record/edit progress from
// admin/kpi/show.tsx; key_result_area users do the same from their own show
// page) — hence the combined role list rather than a single role.
Route::middleware(['auth', 'role:admin,key_result_area'])->group(function () {
    Route::resource('progress', KpiProgressController::class)
        ->only(['store', 'update', 'destroy']);
});

// Viewer Routes
//
// Read-only oversight role. Its index/show access to strategic-plans, kra,
// subkra, and kpi is registered above in the shared 'role:admin,viewer'
// group (same controllers, same views as admin — just no write verbs). This
// group is for anything that ends up being viewer-only and doesn't belong
// under the shared group above.
Route::middleware(['auth', 'role:viewer'])->group(function () {
    //
});

require __DIR__ . '/settings.php';
