<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch; // Pastikan Model Batch di-import
use App\Services\TraceabilityService; // Pastikan Service di-import
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    /**
     * Tampilkan halaman utama operator scan
     */
    public function index()
    {
        // Untuk saat ini, kita hanya tampilkan view-nya
        // Anda bisa tambahkan data 'tasks' nanti
        $tasks = []; // Contoh
        return view('scan.index', compact('tasks'));
    }

    /**
     * Tampilkan halaman checkout
     */
    public function showCheckout()
    {
        return view('scan.checkout');
    }

    /**
     * Proses data checkout
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'tag_uid' => 'required|string',
            'gps_location' => 'nullable|string',
            'photo' => 'nullable|image|max:2048', // Contoh validasi upload
            'notes' => 'nullable|string',
        ]);

        $batch = Batch::where('rfid_tag_uid', $request->tag_uid)->firstOrFail();
        
        // Cek apakah batch siap dikirim
        if ($batch->status !== 'ready_to_ship') {
             return back()->withErrors(['tag_uid' => 'Batch ini tidak siap untuk dikirim.']);
        }

        $data = $request->all();
        
        // Logika upload foto (jika ada)
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('evidence', 'public');
        }

        // Gunakan TraceabilityService
        $result = $this->traceabilityService->processCheckout(
            $batch, 
            $data, 
            Auth::id()
        );

        if ($result['success']) {
            return redirect()->route('scan.index')->with('success', 'Batch berhasil di-checkout.');
        } else {
            return back()->withErrors(['error' => $result['message']]);
        }
    }

    /**
     * Tampilkan halaman checkin
     */
    public function showCheckin()
    {
        return view('scan.checkin');
    }
    
    /**
     * Proses data checkin
     */
    public function processCheckin(Request $request)
    {
        // Mirip dengan processCheckout, Anda perlu menambahkan logika
        // untuk memvalidasi dan memanggil $this->traceabilityService->processCheckin(...)
        
        return redirect()->route('scan.index')->with('success', 'Batch berhasil di-checkin (LOGIKA STUB).');
    }

    /**
     * Tampilkan daftar tugas
     */
    public function tasks()
    {
        return view('scan.tasks'); // Anda harus membuat view ini
    }

    /**
     * Tampilkan riwayat scan
     */
    public function history()
    {
        return view('scan.history'); // Anda harus membuat view ini
    }

    /**
     * Endpoint untuk sinkronisasi offline
     */
    public function syncOffline(Request $request)
    {
        // Logika untuk menerima data offline
        return response()->json(['success' => true, 'message' => 'Sync received.']);
    }
}