namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Batch;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * Display list of shipments
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

        $shipments = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return view('shipments.index', compact('shipments'));
    }

    /**
     * Show create shipment form
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
            'assigned_operator_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date',
            'vehicle_info' => 'nullable|string|max:255',
            'destination_partner_id' => 'required|exists:partners,id',
        ]);

        $validated['status'] = 'scheduled';

        $shipment = Shipment::create($validated);

        // Update batch status
        Batch::find($validated['batch_id'])->update([
            'status' => 'ready_to_ship'
        ]);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment berhasil dijadwalkan.');
    }

    /**
     * Show shipment detail
     */
    public function show(Shipment $shipment)
    {
        $shipment->load([
            'batch.productCode',
            'assignedOperator',
            'destinationPartner'
        ]);

        return view('shipments.show', compact('shipment'));
    }

    /**
     * Show edit shipment form
     */
    public function edit(Shipment $shipment)
    {
        $operators = User::where('role', 'operator')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $partners = Partner::approved()->orderBy('name')->get();

        return view('shipments.edit', compact('shipment', 'operators', 'partners'));
    }

    /**
     * Update shipment
     */
    public function update(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'assigned_operator_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date',
            'vehicle_info' => 'nullable|string|max:255',
            'destination_partner_id' => 'required|exists:partners,id',
        ]);

        $shipment->update($validated);

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment berhasil diupdate.');
    }

    /**
     * Delete shipment
     */
    public function destroy(Shipment $shipment)
    {
        if ($shipment->status !== 'scheduled') {
            return back()->with('error', 'Hanya shipment dengan status "scheduled" yang dapat dihapus.');
        }

        $shipment->delete();

        return redirect()->route('shipments.index')
            ->with('success', 'Shipment berhasil dihapus.');
    }
}