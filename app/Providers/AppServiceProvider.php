<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch; // <-- Pastikan ini ditambahkan

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register TraceabilityService
        $this->app->singleton(\App\Services\TraceabilityService::class);
    }

    public function boot(): void
    {
        // Use Bootstrap 5 for pagination
        Paginator::useBootstrapFive();

        // === UBAH BLOK INI ===
        // Berbagi variabel ke SEMUA view (termasuk layout)
        View::composer('*', function ($view) {
            
            // Set semua nilai default ke 0 untuk tamu
            $stats = [
                'totalBatchesActive' => 0,
                'batchesInTransit' => 0,
                'batchesProcessed' => 0,
                'batchesDelivered' => 0,
            ];

            // Hanya jalankan query jika user sudah login
            // dan pastikan class Batch ada
            if (Auth::check() && class_exists(Batch::class)) {
                $stats['totalBatchesActive'] = Batch::whereIn('status', ['active', 'shipped', 'received'])->count();
                $stats['batchesInTransit'] = Batch::where('status', 'shipped')->count();
                // Asumsi 'status' dari seeder Anda, Anda mungkin perlu menyesuaikannya
                $stats['batchesProcessed'] = Batch::where('status', 'processed')->count(); 
                $stats['batchesDelivered'] = Batch::where('status', 'delivered')->count();
            }
            
            // Kirim semua variabel ini ke semua view
            $view->with($stats);
        });
        // === AKHIR BLOK ===
    }
}