<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('app:status', function () {
    $this->info('=== Sistem Status ===');
    $this->info('Total Users: ' . \App\Models\User::count());
    $this->info('Total Batches: ' . \App\Models\Batch::count());
    $this->info('Active Devices: ' . \App\Models\Device::where('is_active', true)->count());
    $this->info('==================');
})->purpose('Display application status');

Artisan::command('app:reset-demo', function () {
    if ($this->confirm('Reset demo data? This will delete all batches!')) {
        \App\Models\Batch::truncate();
        \App\Models\BatchLog::truncate();
        \App\Models\RfidWrite::truncate();
        \App\Models\Shipment::truncate();
        \App\Models\Document::truncate();

        $this->info('Demo data reset complete!');
    }
})->purpose('Reset demo data');