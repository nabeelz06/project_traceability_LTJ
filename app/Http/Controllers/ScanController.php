<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Services\TraceabilityService;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    /**
     * Halaman utama operator
     */
    public function index()
    {
        $stats = [
            'today_scans' => 0, // TODO: implement counter
            'today_checkout' => 0,
            'today_checkin' => 0,
        ];

        return view('scan.index', compact('stats'));
    }

    /**
     * Halaman checkout
     */
    public function showCheckout()
    {
        return view('scan.checkout');
    }

    /**
     * Proses checkout
     */
    public function processCheckout(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'gps_location' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        // Cari batch berdasarkan RFID tag
        $batch = Batch::where('rfid_tag_uid', $validated['tag_uid'])->first();

        if (!$batch) {
            return back()->withErrors(['tag_uid' => 'Tag RFID tidak terdaftar dalam sistem.']);
        }

        // Validasi batch bisa di-checkout
        if (!$batch->canBeCheckedOut()) {
            return back()->withErrors(['tag_uid' => 'Batch tidak dapat dikirim. Status saat ini: ' . $batch->getStatusLabel()]);
        }

        $data = $validated;

        // Upload foto jika ada
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('checkout_photos', 'public');
        }

        // Proses checkout via service
        $result = $this->traceabilityService->processCheckout($batch, $data, Auth::id());

        if ($result['success']) {
            return redirect()->route('scan.index')
                ->with('success', 'Batch ' . $batch->batch_code . ' berhasil di-checkout.');
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Halaman checkin
     */
    public function showCheckin()
    {
        return view('scan.checkin');
    }

    /**
     * Proses checkin
     */
    public function processCheckin(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        // Cari batch berdasarkan RFID tag
        $batch = Batch::where('rfid_tag_uid', $validated['tag_uid'])->first();

        if (!$batch) {
            return back()->withErrors(['tag_uid' => 'Tag RFID tidak terdaftar dalam sistem.']);
        }

        // Validasi batch bisa di-checkin
        if (!$batch->canBeCheckedIn()) {
            return back()->withErrors(['tag_uid' => 'Batch tidak dapat di-check-in. Status saat ini: ' . $batch->getStatusLabel()]);
        }

        // Proses checkin via service
        // Partner ID akan diambil dari user yang login (jika mitra) atau tetap di PT Timah
        $partnerId = Auth::user()->partner_id ?? null;
        
        $result = $this->traceabilityService->processCheckin($batch, $partnerId, $validated, Auth::id());

        if ($result['success']) {
            return redirect()->route('scan.index')
                ->with('success', 'Batch ' . $batch->batch_code . ' berhasil di-check-in.');
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Task list operator
     */
    public function tasks()
    {
        // TODO: Implement task management
        $tasks = [];
        return view('scan.tasks', compact('tasks'));
    }

    /**
     * History scan operator
     */
    public function history()
    {
        $user = Auth::user();
        
        $history = \App\Models\BatchLog::where('actor_user_id', $user->id)
            ->whereIn('action', ['checked_out', 'checked_in'])
            ->with(['batch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('scan.history', compact('history'));
    }
}