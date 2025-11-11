<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProductCodeController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\MitraBatchController; // <-- TAMBAHKAN INI

// --- Rute Guest (Belum Login) ---
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// --- Rute Yang Sudah Login ---
Route::middleware(['auth', 'user.active'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/traceability/search', [TraceabilityController::class, 'search'])->name('traceability.search');
    Route::get('/traceability/tree/{batch}', [TraceabilityController::class, 'tree'])->name('traceability.tree');
    
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // --- Rute Admin & Super Admin ---
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('batches/create', [BatchController::class, 'create'])->name('batches.create');
        Route::post('batches', [BatchController::class, 'store'])->name('batches.store');
        Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
        Route::put('batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
        Route::put('batches/{batch}/mark-ready', [BatchController::class, 'markReady'])->name('batches.mark-ready');
        
        Route::post('batches/{batch}/write-rfid', [BatchController::class, 'writeRfid'])->name('batches.write-rfid');
        Route::post('batches/{batch}/verify-rfid', [BatchController::class, 'verifyRfid'])->name('batches.verify-rfid');
        
        Route::resource('shipments', ShipmentController::class);
        Route::get('reports/operational', [ReportController::class, 'operational'])->name('reports.operational');
    });

    // --- Rute Super Admin ---
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
    
    // --- Rute Operator ---
    Route::middleware('role:operator')->prefix('scan')->name('scan.')->group(function () {
        Route::get('/', [ScanController::class, 'index'])->name('index');
        Route::get('checkout', [ScanController::class, 'showCheckout'])->name('checkout');
        Route::post('checkout', [ScanController::class, 'processCheckout'])->name('checkout.process');
        Route::get('checkin', [ScanController::class, 'showCheckin'])->name('checkin');
        Route::post('checkin', [ScanController::class, 'processCheckin'])->name('checkin.process');
        Route::get('tasks', [ScanController::class, 'tasks'])->name('tasks');
        Route::get('history', [ScanController::class, 'history'])->name('history');
        Route::post('sync', [ScanController::class, 'syncOffline'])->name('sync');
    });
    
    // --- Rute Mitra Middlestream ---
    Route::middleware('role:mitra_middlestream')->prefix('mitra')->name('mitra.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'mitraDashboard'])->name('dashboard');
        
        // --- Gunakan MitraBatchController ---
        Route::post('batches/{batch}/checkin', [MitraBatchController::class, 'mitraCheckin'])->name('batches.checkin');
        Route::get('batches/{batch}/create-child', [MitraBatchController::class, 'createChild'])->name('batches.create-child');
        Route::post('batches/{batch}/store-child', [MitraBatchController::class, 'storeChild'])->name('batches.store-child');
        Route::post('batches/{batch}/checkout', [MitraBatchController::class, 'mitraCheckout'])->name('batches.checkout');
        Route::post('batches/{batch}/upload-document', [MitraBatchController::class, 'uploadDocument'])->name('batches.upload-document');
        Route::get('reports', [ReportController::class, 'mitraReports'])->name('reports');
    });
    
    // --- Rute Mitra Downstream ---
    Route::middleware('role:mitra_downstream')->prefix('downstream')->name('downstream.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'downstreamDashboard'])->name('dashboard');
        
        // --- Gunakan MitraBatchController ---
        Route::post('batches/{batch}/checkin-final', [MitraBatchController::class, 'downstreamCheckin'])->name('batches.checkin-final');
        Route::get('batches', [BatchController::class, 'downstreamBatches'])->name('batches.index');
    });
    
    // --- Rute Auditor ---
    Route::middleware('role:auditor')->prefix('audit')->name('audit.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'auditorDashboard'])->name('dashboard');
        Route::get('batches', [BatchController::class, 'auditorBatches'])->name('batches.index');
        Route::get('batches/{batch}', [BatchController::class, 'auditorBatchDetail'])->name('batches.show');
        Route::post('batches/{batch}/export-evidence', [ReportController::class, 'exportEvidence'])->name('batches.export-evidence');
        Route::get('logs', [ReportController::class, 'auditLogs'])->name('logs');
    });

    // --- Rute Detail Batch (Harus diletakkan di akhir) ---
    // Rute ini harus bisa diakses oleh BANYAK role, jadi diletakkan di luar grup spesifik
    Route::get('batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
});