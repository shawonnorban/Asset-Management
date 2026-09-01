<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssignmentAuditController;
use App\Http\Controllers\SoftwareLicenseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserStatusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Depreciation\DepreciationController;
use App\Http\Controllers\Depreciation\DepreciationSettingController;
use App\Http\Controllers\Depreciation\DepreciationDisposalController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\IssueReport\ReportIssueController;
use App\Http\Controllers\IssueReport\IncomingReportController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\DisposalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;


// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();
Route::pattern('asset', '[0-9]+');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::middleware('permission:reports.create')->group(function () {
        Route::get('/report-issue', [ReportIssueController::class, 'index'])->name('report-issue.index');
        Route::get('/search-assets', [ReportIssueController::class, 'searchAssets'])->name('search-assets');
        Route::get('/asset-data', [ReportIssueController::class, 'getAssetData'])->name('asset-data');
        Route::post('/report-issue', [ReportIssueController::class, 'store'])->name('report-issue.store');
    });
    Route::middleware('permission:reports.view_own')->group(function () {
        Route::get('/my-reports', [ReportIssueController::class, 'mine'])->name('my-reports.index');
        Route::get('/my-reports/{id}', [ReportIssueController::class, 'showMine'])->whereNumber('id')->name('my-reports.show');
    });
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/incoming-reports', [IncomingReportController::class, 'index'])->name('incoming-reports.index');
        Route::get('/incoming-reports/detail/{id}', [IncomingReportController::class, 'detail'])->name('incoming-reports.detail');
    });
    Route::post('/incoming-reports/detail/{id}/review', [IncomingReportController::class, 'review'])->middleware('permission:reports.review|reports.manage')->name('incoming-reports.review');
    Route::put('/incoming-reports/detail/{id}/complete', [IncomingReportController::class, 'complete'])->middleware('permission:reports.complete|reports.manage')->name('incoming-reports.complete');

    Route::group(['middleware'  => 'permission:dashboard.view'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/home', [DashboardController::class, 'index'])->name('home');
    });

    Route::group(['middleware'  => 'permission:stock.view'], function () {
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    });

    Route::group(['middleware'  => 'permission:categories.view'], function () {
        Route::resource('/categories', CategoryController::class)->only(['index', 'show']);
    });

    Route::group(['middleware'  => 'permission:locations.view'], function () {
        Route::resource('/locations', LocationController::class)->only(['index', 'show']);
    });

    Route::group(['middleware'  => 'permission:employees.view'], function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])
            ->whereNumber('id')->name('employees.show');
    });

    Route::group(['middleware'  => 'permission:depreciation.view'], function () {
        Route::get('/depreciation', [DepreciationController::class, 'index'])->name('depreciation.index');
        Route::get('/depreciation/{asset}', [DepreciationController::class, 'show'])->name('depreciation.show');
        Route::get('/depreciation/{asset}/export-pdf', [DepreciationController::class, 'exportPdf'])->name('depreciation.export-pdf');
    });

    Route::group(['middleware' => 'permission:maintenance.view'], function () {
        Route::resource('/maintenance', MaintenanceController::class)->only(['index']);
    });

    Route::group(['middleware' => 'permission:maintenance.view'], function () {
        Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index'])->name('maintenance-requests.index');
        Route::get('/maintenance-requests/create', [MaintenanceRequestController::class, 'create'])->name('maintenance-requests.create');
        Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store'])->name('maintenance-requests.store');
        Route::get('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])->name('maintenance-requests.show');
    });

    Route::group(['middleware' => 'permission:maintenance.manage'], function () {
        Route::get('/maintenance-requests/{maintenanceRequest}/edit', [MaintenanceRequestController::class, 'edit'])->name('maintenance-requests.edit');
        Route::put('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'update'])->name('maintenance-requests.update');
        Route::patch('/maintenance-requests/{maintenanceRequest}/assign', [MaintenanceRequestController::class, 'assign'])->name('maintenance-requests.assign');
    });

    Route::group(['middleware' => 'permission:maintenance.view'], function () {
        Route::get('/warranties', [WarrantyController::class, 'index'])->name('warranties.index');
        Route::get('/warranties/create', [WarrantyController::class, 'create'])->name('warranties.create');
        Route::post('/warranties', [WarrantyController::class, 'store'])->name('warranties.store');
        Route::get('/warranties/{warranty}', [WarrantyController::class, 'show'])->name('warranties.show');
    });

    Route::group(['middleware' => 'permission:transfers.view'], function () {
        Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
        Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
    });

    Route::group(['middleware' => 'permission:transfers.manage'], function () {
        Route::post('/transfers/{transfer}/approve', [TransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfers/{transfer}/reject', [TransferController::class, 'reject'])->name('transfers.reject');
    });

    Route::group(['middleware' => 'permission:disposals.view'], function () {
        Route::get('/disposals', [DisposalController::class, 'index'])->name('disposals.index');
        Route::get('/disposals/create', [DisposalController::class, 'create'])->name('disposals.create');
        Route::post('/disposals', [DisposalController::class, 'store'])->name('disposals.store');
        Route::get('/disposals/{disposal}', [DisposalController::class, 'show'])->name('disposals.show');
    });

    Route::group(['middleware' => 'permission:disposals.manage'], function () {
        Route::post('/disposals/{disposal}/approve', [DisposalController::class, 'approve'])->name('disposals.approve');
        Route::post('/disposals/{disposal}/reject', [DisposalController::class, 'reject'])->name('disposals.reject');
    });

    Route::group(['middleware' => 'permission:notifications.view'], function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });

    Route::group(['middleware' => 'permission:reports.view'], function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/maintenance', [ReportController::class, 'maintenance'])->name('reports.maintenance');
        Route::get('/reports/warranty', [ReportController::class, 'warranty'])->name('reports.warranty');
        Route::get('/reports/movement', [ReportController::class, 'movement'])->name('reports.movement');
        Route::get('/reports/{type}/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::group(['middleware'  => 'permission:assets.view'], function () {
        // URL is /inventory because public/assets/ holds the theme files and
        // would shadow an /assets route. Route names stay assets.* .
        Route::resource('/inventory', AssetController::class)->only(['index', 'show'])
            ->parameters(['inventory' => 'asset'])
            ->names('assets');
        Route::get('/inventory/{asset}/lifecycle', [AssetController::class, 'lifecycle'])->whereNumber('asset')->middleware('permission:assets.view|assets.manage')->name('assets.lifecycle');
        Route::get('/inventory/{asset}/lifecycle/export', [AssetController::class, 'exportLifecycle'])->whereNumber('asset')->middleware('permission:assets.view|assets.manage')->name('assets.lifecycle.export');
        Route::get('/inventory/export/pdf', [AssetController::class, 'exportPdf'])->middleware('permission:assets.export|assets.manage')->name('assets.export.pdf');
        Route::get('/inventory/export/full-report', [AssetController::class, 'exportFullReport'])->middleware('permission:assets.export|assets.manage')->name('assets.export.full');
        Route::get('/inventory/export/excel', [AssetController::class, 'exportExcel'])->middleware('permission:assets.export|assets.manage')->name('assets.export.excel');
    });

    Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])
        ->whereNumber('maintenance')
        ->middleware('permission:maintenance.view')
        ->name('maintenance.show');

    Route::get('/inventory/create', [AssetController::class, 'create'])->middleware('permission:assets.create|assets.manage')->name('assets.create');
    Route::post('/inventory', [AssetController::class, 'store'])->middleware('permission:assets.create|assets.manage')->name('assets.store');
    Route::get('/inventory/{asset}/edit', [AssetController::class, 'edit'])->whereNumber('asset')->middleware('permission:assets.edit|assets.manage')->name('assets.edit');
    Route::match(['put', 'patch'], '/inventory/{asset}', [AssetController::class, 'update'])->whereNumber('asset')->middleware('permission:assets.update|assets.manage')->name('assets.update');
    Route::delete('/inventory/{asset}', [AssetController::class, 'destroy'])->whereNumber('asset')->middleware('permission:assets.delete|assets.manage')->name('assets.destroy');

    Route::get('/categories/create', [CategoryController::class, 'create'])->middleware('permission:categories.create|categories.manage')->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:categories.create|categories.manage')->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('permission:categories.edit|categories.manage')->name('categories.edit');
    Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update|categories.manage')->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete|categories.manage')->name('categories.destroy');

    Route::get('/locations/create', [LocationController::class, 'create'])->middleware('permission:locations.create|locations.manage')->name('locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->middleware('permission:locations.create|locations.manage')->name('locations.store');
    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->middleware('permission:locations.edit|locations.manage')->name('locations.edit');
    Route::match(['put', 'patch'], '/locations/{location}', [LocationController::class, 'update'])->middleware('permission:locations.update|locations.manage')->name('locations.update');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->middleware('permission:locations.delete|locations.manage')->name('locations.destroy');

    Route::group(['middleware' => 'permission:maintenance.manage'], function () {
        Route::resource('/maintenance', MaintenanceController::class)->except(['show', 'index']);
        Route::patch('/maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
    });

    Route::group(['middleware' => 'permission:depreciation.manage'], function () {
        Route::post('/depreciation/{asset}/depreciate', [DepreciationController::class, 'depreciate'])->name('depreciation.depreciate');
    });

    Route::group(['middleware'  => 'permission:employees.manage'], function () {
        //--employees--//
        Route::get('/employees/create', [EmployeeController::class, 'create'])->middleware('permission:employees.create|employees.manage')->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employees.create|employees.manage')->name('employees.store');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->middleware('permission:employees.edit|employees.manage')->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->middleware('permission:employees.update|employees.manage')->name('employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete|employees.manage')->name('employees.destroy');

        //--departments & positions--//
        Route::middleware('permission:departments.view|departments.manage|employees.manage')->group(function () {
            Route::resource('/departments', DepartmentController::class)->except(['show']);
        });
        Route::middleware('permission:positions.view|positions.manage|employees.manage')->group(function () {
            Route::resource('/positions', PositionController::class)->except(['show']);
        });

        //--asset handover--//
        Route::middleware('permission:assignments.view|assignments.manage')->group(function () {
            Route::get('/assignments', [AssetAssignmentController::class, 'index'])->name('assignments.index');
        });
        Route::middleware('permission:assignments.assign|assignments.manage')->group(function () {
            Route::get('/assignments/create', [AssetAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('/assignments', [AssetAssignmentController::class, 'store'])->name('assignments.store');
            Route::post('/inventory/{asset}/assign', [AssetAssignmentController::class, 'store'])->name('assignments.store.legacy');
        });
        Route::middleware('permission:assignments.return|assignments.manage')->group(function () {
            Route::put('/inventory/{asset}/return', [AssetAssignmentController::class, 'returnAsset'])->name('assignments.return');
        });

        //--assignment audits--//
        Route::get('/assignment-audits', [AssignmentAuditController::class, 'index'])->middleware('permission:assignment_audits.view')->name('assignment-audits.index');
        Route::get('/assignment-audits/create', [AssignmentAuditController::class, 'create'])->middleware('permission:assignment_audits.create')->name('assignment-audits.create');
        Route::post('/assignment-audits', [AssignmentAuditController::class, 'store'])->middleware('permission:assignment_audits.create')->name('assignment-audits.store');
        Route::get('/assignment-audits/{audit}', [AssignmentAuditController::class, 'show'])->middleware('permission:assignment_audits.view')->name('assignment-audits.show');
        Route::post('/assignment-audits/{audit}/start', [AssignmentAuditController::class, 'start'])->middleware('permission:assignment_audits.manage')->name('assignment-audits.start');
        Route::post('/assignment-audits/{audit}/verify', [AssignmentAuditController::class, 'verify'])->middleware('permission:assignment_audits.verify')->name('assignment-audits.verify');
        Route::post('/assignment-audits/{audit}/complete', [AssignmentAuditController::class, 'complete'])->middleware('permission:assignment_audits.manage')->name('assignment-audits.complete');
        Route::get('/assignment-audits/{audit}/report', [AssignmentAuditController::class, 'report'])->middleware('permission:assignment_audits.view')->name('assignment-audits.report');

        //--software licenses--//
        Route::middleware('permission:software_licenses.view|software_licenses.manage')->group(function () {
            Route::resource('/software-licenses', SoftwareLicenseController::class)
                ->parameters(['software-licenses' => 'softwareLicense']);
        });
        Route::middleware('permission:software_licenses.manage')->group(function () {
            Route::post('/inventory/{asset}/software', [SoftwareLicenseController::class, 'install'])->name('software-licenses.install');
            Route::put('/inventory/{asset}/software/{assignment}', [SoftwareLicenseController::class, 'uninstall'])->name('software-licenses.uninstall');
        });

        //--disposal--//
    });

    Route::group(['middleware' => 'permission:depreciation.manage'], function () {
        Route::get('/depreciation-settings', [DepreciationSettingController::class, 'index'])->name('depreciation-settings.index');
        Route::get('/depreciation-settings/create', [DepreciationSettingController::class, 'create'])->name('depreciation-settings.create');
        Route::post('/depreciation-settings', [DepreciationSettingController::class, 'store'])->name('depreciation-settings.store');
        Route::get('/depreciation-settings/{asset}/edit', [DepreciationSettingController::class, 'edit'])->name('depreciation-settings.edit');
        Route::put('/depreciation-settings/{asset}', [DepreciationSettingController::class, 'update'])->name('depreciation-settings.update');
        Route::get('/depreciation/{asset}/disposal', [DepreciationDisposalController::class, 'create'])->name('depreciation.dispose.form');
        Route::put('/depreciation/{asset}/disposal', [DepreciationDisposalController::class, 'store'])->name('depreciation.dispose.store');
    });


    Route::middleware(['auth', 'permission:users.view|users.manage'])->group(function () {
        //--user control--//
        Route::resource('/users', UserController::class);
        Route::get('/user/status', [UserStatusController::class, 'index'])->name('users.status');

        //--audit--//
        Route::get('/audit', [AuditTrailController::class, 'index'])->name('audit.index');
        Route::get('/audit/{auditLog}', [AuditTrailController::class, 'show'])->name('audit.show');
    });

    Route::middleware(['auth', 'permission:roles.view|roles.manage'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
