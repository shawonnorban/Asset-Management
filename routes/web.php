<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\SoftwareLicenseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStatusController;
use App\Http\Controllers\Depreciation\DepreciationController;
use App\Http\Controllers\Depreciation\DepreciationSettingController;
use App\Http\Controllers\Depreciation\DepreciationDisposalController;
use App\Http\Controllers\StockTake\StockTakeController;
use App\Http\Controllers\StockTake\StockTakeDetailController;
use App\Http\Controllers\IssueReport\ReportIssueController;
use App\Http\Controllers\IssueReport\IncomingReportController;
use App\Http\Controllers\IssueReport\ReviewReportController;
use App\Http\Controllers\IssueReport\CompletedReportController;
use App\Http\Controllers\AuditTrailController;


// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();
Route::middleware('auth')->group(function () {
    Route::group(['middleware'  => 'CheckRole:admin,staff,manager'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/home', [DashboardController::class, 'index'])->name('home');
        Route::resource('/categories', CategoryController::class);
        Route::resource('/locations', LocationController::class);
        // URL is /inventory because public/assets/ holds the theme files and
        // would shadow an /assets route. Route names stay assets.* .
        Route::resource('/inventory', AssetController::class)
            ->parameters(['inventory' => 'asset'])
            ->names('assets');
        Route::get('/inventory/export/pdf', [AssetController::class, 'exportPdf'])->name('assets.export.pdf');
        Route::get('/inventory/export/full-report', [AssetController::class, 'exportFullReport'])->name('assets.export.full');
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        // numeric only, otherwise this would swallow /employees/create
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])
            ->whereNumber('id')->name('employees.show');
        Route::get('/inventory/export/excel', [AssetController::class, 'exportExcel'])->name('assets.export.excel');

        //--review reports--//
        Route::get('/review-reports', [ReviewReportController::class, 'index'])->name('review-reports.index');
        Route::get('/review-reports/detail/{issueReport}', [ReviewReportController::class, 'detail']);
        Route::post('/review-reports/detail/{issueReport}', [ReviewReportController::class, 'store']);

        //--depreciation--//
        Route::get('/depreciation', [DepreciationController::class, 'index'])->name('depreciation.index');
        Route::get('/depreciation/{asset}', [DepreciationController::class, 'show'])->name('depreciation.show');
        Route::get('/depreciation/{asset}/export-pdf', [DepreciationController::class, 'exportPdf'])->name('depreciation.export-pdf');
        Route::post('/depreciation/{asset}/depreciate', [DepreciationController::class, 'depreciate'])->name('depreciation.depreciate');


        //--stock takes--//
        Route::get('/stock-takes/get-asset-by-qr', [StockTakeDetailController::class, 'getAssetData'])->name('stock-takes.getAssetData');
        Route::get('/stock-takes', [StockTakeController::class, 'index'])->name('stock-takes.index');
        Route::get('/create-stock-take', [StockTakeController::class, 'create'])->name('stock-takes.create');
        Route::post('/stock-takes', [StockTakeController::class, 'store'])->name('stock-takes.store');
        Route::get('/stock-takes/{stockTake}', [StockTakeController::class, 'show'])->name('stock-takes.show');
        Route::put('/stock-takes/{stockTake}/final', [StockTakeController::class, 'finalize'])->name('stock-takes.final');
        Route::get('/stock-takes/{stockTake}/pdf', [StockTakeController::class, 'pdf'])->name('stock-takes.pdf');

        //--stock take asset input--//
        Route::get('/stock-takes/{stockTake}/input', [StockTakeDetailController::class, 'create'])->name('stock-takes.input');
        Route::post('/stock-takes/{stockTake}/input', [StockTakeDetailController::class, 'store'])->name('stock-takes.input.store');
        Route::delete('/stock-takes/{stockTake}/detail/{detail}', [StockTakeDetailController::class, 'destroy'])->name('stock-takes.detail.destroy');

    });

    Route::group(['middleware'  => 'CheckRole:admin,staff'], function () {
        //--report an issue--//
        Route::get('/report-issue', [ReportIssueController::class, 'index'])->name('report-issue.index');
        Route::get('/get-asset-data', [ReportIssueController::class, 'getAssetData']);
        Route::post('/report-issue', [ReportIssueController::class, 'store'])->name('report-issue.store');
    });

    Route::group(['middleware'  => 'CheckRole:admin,manager'], function () {
        //--incoming reports--//
        Route::get('/incoming-reports', [IncomingReportController::class, 'index'])->name('incoming-reports.index');
        Route::get('/incoming-reports/detail/{id}', [IncomingReportController::class, 'detail']);
        Route::put('/incoming-reports/detail/{id}/review', [IncomingReportController::class, 'review']);
        Route::put('/incoming-reports/detail/{id}/complete', [IncomingReportController::class, 'complete']);

        //--completed reports--//
        Route::get('/completed-reports', [CompletedReportController::class, 'index'])->name('completed-reports.index');
        Route::get('/completed-reports/print-report/{id}', [CompletedReportController::class, 'printReport']);

        //--employees--//
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        //--departments & positions--//
        Route::resource('/departments', DepartmentController::class)->except(['show']);
        Route::resource('/positions', PositionController::class)->except(['show']);

        //--asset handover--//
        Route::get('/assignments', [AssetAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/inventory/{asset}/assign', [AssetAssignmentController::class, 'store'])->name('assignments.store');
        Route::put('/inventory/{asset}/return', [AssetAssignmentController::class, 'returnAsset'])->name('assignments.return');

        //--software licenses--//
        Route::resource('/software-licenses', SoftwareLicenseController::class)
            ->parameters(['software-licenses' => 'softwareLicense']);
        Route::post('/inventory/{asset}/software', [SoftwareLicenseController::class, 'install'])->name('software-licenses.install');
        Route::put('/inventory/{asset}/software/{assignment}', [SoftwareLicenseController::class, 'uninstall'])->name('software-licenses.uninstall');

        //--depreciation settings--//
        Route::get('/depreciation-settings', [DepreciationSettingController::class, 'index'])->name('depreciation-settings.index');
        Route::get('/depreciation-settings/create', [DepreciationSettingController::class, 'create'])->name('depreciation-settings.create');
        Route::post('/depreciation-settings', [DepreciationSettingController::class, 'store'])->name('depreciation-settings.store');
        Route::get('/depreciation-settings/{asset}/edit', [DepreciationSettingController::class, 'edit'])->name('depreciation-settings.edit');
        Route::put('/depreciation-settings/{asset}', [DepreciationSettingController::class, 'update'])->name('depreciation-settings.update');

        //--disposal--//
        Route::get('/depreciation/{asset}/disposal', [DepreciationDisposalController::class, 'create'])->name('depreciation.dispose.form');
        Route::put('/depreciation/{asset}/disposal', [DepreciationDisposalController::class, 'store'])->name('depreciation.dispose.store');

    });


    Route::middleware(['auth', 'CheckRole:admin'])->group(function () {
        //--user control--//
        Route::resource('/users', UserController::class);
        Route::get('/user/status', [UserStatusController::class, 'index'])->name('users.status');

        //--audit--//
        Route::get('/audit', [AuditTrailController::class, 'index'])->name('audit.index');
        Route::get('/audit/{auditLog}', [AuditTrailController::class, 'show'])->name('audit.show');
    });
});
