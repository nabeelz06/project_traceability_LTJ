<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Batch;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    /**
     * Display list shipments
     */
    public function index(Request $request)
    {
        $query = Shipment::with(['batch.productCode', 'assignedOperator', 'destinationPartner']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        // Search by batch code
        if ($request->filled('search')) {
            $query->whereHas('batch', function($q) use ($request) {
                $q->where('batch_code', 'like', '%' . $request->search . '%');
            });
        }

        $shipments = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return view('shipments.index', compact('shipments'));
    }

    /**
     * Show form create shipment
     */
    public function create()
    {
        // Get batches yang ready to ship
        $batches = Batch::whereIn('status', ['ready_to_ship', 'created'])
            ->with('productCode')
            ->orderBy('created_at', 'desc')
            ->get();

        $operators = User::where('role', 'operator')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $partners = Partner::approved()->orderBy('name')->get();

        return view('shipments.create', compact('batches', 'operators', 'partners'));
    }

    /**
     * Store new shipment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'destination_partner_id' => 'required|exists:partners,id',
            'assigned_operator_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date|after_or_equal:today',
            'vehicle_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $validated['status'] = 'scheduled';

            $shipment = Shipment::create($validated);

            // Update batch status
            Batch::find($validated['batch_id'])->update([
                'status' => 'ready_to_ship'
            ]);

            DB::commit();

            return redirect()->route('shipments.index')
                ->with('success', 'Pengiriman berhasil dijadwalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menjadwalkan pengiriman: ' . $e->getMessage());
        }
    }

    /**
     * Show detail shipment
     */
    public function show(Shipment $shipment)
    {
        $shipment->load(['batch.productCode', 'assignedOperator', 'destinationPartner']);
        
        return view('shipments.show', compact('shipment'));
    }

    /**
     * Update shipment
     */
    public function update(Request $request, Shipment $shipment)
    {
        if (!in_array($shipment->status, ['scheduled'])) {
            return back()->with('error', 'Shipment tidak dapat diubah.');
        }

        $validated = $request->validate([
            'assigned_operator_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date',
            'vehicle_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $shipment->update($validated);

        return back()->with('success', 'Shipment berhasil diperbarui.');
    }

    /**
     * Cancel shipment
     */
    public function destroy(Shipment $shipment)
    {
        if (!in_array($shipment->status, ['scheduled'])) {
            return back()->with('error', 'Shipment tidak dapat dibatalkan.');
        }

        $shipment->update(['status' => 'cancelled']);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment berhasil dibatalkan.');
    }
}