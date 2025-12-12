<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProductCodeController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\MitraBatchController;

// Import Controller Khusus Operator
use App\Http\Controllers\WetProcessController;
use App\Http\Controllers\DryProcessController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\RegulatorController;
use App\Http\Controllers\PDFReportController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Password Reset Routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware(['auth', 'user.active'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard Utama (Admin/Super Admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    
    Route::get('/traceability/search', [TraceabilityController::class, 'search'])->name('traceability.search');
    Route::get('/traceability/tree/{batch}', [TraceabilityController::class, 'tree'])->name('traceability.tree');
    Route::get('/traceability/chain/{batch}', [TraceabilityController::class, 'getChain'])->name('traceability.chain');
    
    // Role: Super Admin & Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('batches/create', [BatchController::class, 'create'])->name('batches.create');
        Route::post('batches', [BatchController::class, 'store'])->name('batches.store');
        Route::get('batches/{batch}/edit', [BatchController::class, 'edit'])->name('batches.edit');
        Route::put('batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
        Route::put('batches/{batch}/mark-ready', [BatchController::class, 'markReady'])->name('batches.mark-ready');
        Route::post('batches/{batch}/write-rfid', [BatchController::class, 'writeRfid'])->name('batches.write-rfid');
        Route::post('batches/{batch}/verify-rfid', [BatchController::class, 'verifyRfid'])->name('batches.verify-rfid');
        Route::delete('batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
        
        Route::resource('shipments', ShipmentController::class);
        Route::get('reports/operational', [ReportController::class, 'operational'])->name('reports.operational');
    });
    
    // General Batch Access
    Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    
    // Role: Super Admin Only
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        
        Route::resource('partners', PartnerController::class);
        Route::post('partners/{partner}/approve', [PartnerController::class, 'approve'])->name('partners.approve');
        Route::post('partners/{partner}/reject', [PartnerController::class, 'reject'])->name('partners.reject');
        
        Route::resource('product-codes', ProductCodeController::class);
        Route::resource('devices', DeviceController::class);
        Route::post('devices/{device}/revoke', [DeviceController::class, 'revoke'])->name('devices.revoke');
        
        Route::put('batches/{batch}/correct', [BatchController::class, 'correct'])->name('batches.correct');
        Route::delete('batches/{batch}/force-delete', [BatchController::class, 'forceDelete'])->name('batches.force-delete');
        
        Route::get('logs/system', [ReportController::class, 'systemLogs'])->name('logs.system');
        Route::get('logs/batch', [ReportController::class, 'batchLogs'])->name('logs.batch');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
    
    // Role: Operator Lapangan (RFID Scan)
    Route::middleware('role:operator')->prefix('scan')->name('scan.')->group(function () {
        Route::get('/', [ScanController::class, 'index'])->name('index');
        Route::get('checkout', [ScanController::class, 'showCheckout'])->name('checkout');
        Route::post('checkout', [ScanController::class, 'processCheckout'])->name('checkout.process');
        Route::get('checkin', [ScanController::class, 'showCheckin'])->name('checkin');
        Route::post('checkin', [ScanController::class, 'processCheckin'])->name('checkin.process');
        Route::get('tasks', [ScanController::class, 'tasks'])->name('tasks');
        Route::get('history', [ScanController::class, 'history'])->name('history');
    });

    // Wet Process Routes
    Route::middleware('role:wet_operator')->prefix('wet-process')->name('wet-process.')->group(function () {
        Route::get('dashboard', [WetProcessController::class, 'dashboard'])->name('dashboard');
        Route::get('batches/create', [WetProcessController::class, 'create'])->name('create');
        Route::post('batches', [WetProcessController::class, 'store'])->name('store');
        Route::get('pending-dispatch', [WetProcessController::class, 'pendingDispatch'])->name('pending-dispatch');
        Route::post('batches/{batch}/dispatch', [WetProcessController::class, 'dispatch'])->name('dispatch');
    });

    // Dry Process Routes
    Route::middleware('role:dry_operator')->prefix('dry-process')->name('dry-process.')->group(function () {
        Route::get('dashboard', [DryProcessController::class, 'dashboard'])->name('dashboard');
        Route::post('batches/{batch}/receive', [DryProcessController::class, 'receive'])->name('receive');
        Route::post('batches/{batch}/stock', [DryProcessController::class, 'stock'])->name('stock');
        Route::post('batches/{batch}/retrieve', [DryProcessController::class, 'retrieve'])->name('retrieve');
        Route::get('batches/{batch}/process', [DryProcessController::class, 'processForm'])->name('process-form');
        Route::post('batches/{batch}/process', [DryProcessController::class, 'process'])->name('process');
        Route::post('batches/{batch}/dispatch-warehouse', [DryProcessController::class, 'dispatchToWarehouse'])->name('dispatch-warehouse');
    });

    // Warehouse Routes
    Route::middleware('role:warehouse_operator')->prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('dashboard', [WarehouseController::class, 'dashboard'])->name('dashboard');
        Route::post('batches/{batch}/receive', [WarehouseController::class, 'receive'])->name('receive');
        Route::get('batches/{batch}/export', [WarehouseController::class, 'exportForm'])->name('export-form');
        Route::post('batches/{batch}/export', [WarehouseController::class, 'exportBatch'])->name('export');
        Route::get('batches/{batch}/split-lab', [WarehouseController::class, 'splitForm'])->name('split-lab-form');
        Route::post('batches/{batch}/split-lab', [WarehouseController::class, 'splitForLab'])->name('split-lab');
        Route::post('batches/{batch}/dispatch-lab', [WarehouseController::class, 'dispatchToLab'])->name('dispatch-lab');
    });

    // Lab Routes
    Route::middleware('role:lab_operator')->prefix('lab')->name('lab.')->group(function () {
        Route::get('dashboard', [LabController::class, 'dashboard'])->name('dashboard');
        Route::post('batches/{batch}/receive', [LabController::class, 'receive'])->name('receive');
        Route::get('batches/{batch}/analysis', [LabController::class, 'analysisForm'])->name('analysis-form');
        Route::post('batches/{batch}/analysis', [LabController::class, 'storeAnalysis'])->name('store-analysis');
        Route::get('batches/{batch}/view-analysis', [LabController::class, 'viewAnalysis'])->name('view-analysis');
    });

    // Mitra Middlestream Routes
    Route::middleware('role:mitra_middlestream')->prefix('mitra')->name('mitra.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'mitraDashboard'])->name('dashboard');
        Route::post('batches/{batch}/checkin', [MitraBatchController::class, 'mitraCheckin'])->name('batches.checkin');
        Route::get('batches/{batch}/create-child', [MitraBatchController::class, 'createChild'])->name('batches.create-child');
        Route::post('batches/{batch}/store-child', [MitraBatchController::class, 'storeChild'])->name('batches.store-child');
        Route::post('batches/{batch}/checkout', [MitraBatchController::class, 'mitraCheckout'])->name('batches.checkout');
        Route::post('batches/{batch}/upload-document', [MitraBatchController::class, 'uploadDocument'])->name('batches.upload-document');
        Route::get('reports', [ReportController::class, 'mitraReports'])->name('reports');
    });
    
    // Mitra Downstream Routes
    Route::middleware('role:mitra_downstream')->prefix('downstream')->name('downstream.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'downstreamDashboard'])->name('dashboard');
        Route::post('batches/{batch}/checkin-final', [MitraBatchController::class, 'downstreamCheckin'])->name('batches.checkin-final');
        Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
    });
    
    // Auditor Routes
    Route::middleware('role:auditor,g_bim,g_esdm')->prefix('audit')->name('audit.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'auditDashboard'])->name('dashboard');
        Route::get('logs/batch', [ReportController::class, 'batchLogs'])->name('logs.batch');
        Route::get('logs/system', [ReportController::class, 'systemLogs'])->name('logs.system');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Regulator Routes (BIM & ESDM)
    Route::middleware('role:g_bim,g_esdm')->prefix('regulator')->name('regulator.')->group(function () {
        Route::get('dashboard', [RegulatorController::class, 'dashboard'])->name('dashboard');
        Route::get('batch/{id}', [RegulatorController::class, 'showBatch'])->name('batch.show'); 
        Route::get('report/download', [PDFReportController::class, 'generateRegulatorReport'])->name('report.download');
    });
});

// Emergency DB Cleaner (Development Only - Remove in Production)
Route::get('/force-reset-db', function () {
    $tables = [
        'system_logs', 'shipments', 'rfid_writes', 'documents', 'batch_logs',
        'batches', 'devices', 'product_codes', 'users', 'partners',
        'personal_access_tokens', 'password_reset_tokens', 'jobs', 'job_batches',
        'failed_jobs', 'cache', 'cache_locks', 'sessions', 'migrations',
    ];

    $log = [];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            Schema::drop($table);
            $log[] = "Dropped: $table";
        } else {
            $log[] = "Skipped (not found): $table";
        }
    }

    return response()->json([
        'message' => 'Database successfully wiped manually',
        'details' => $log
    ]);
});