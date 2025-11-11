<?php

namespace App\HttpKHttp\Controllers;

use App\Models\Batch;
use App\Models\ProductCode;
use App\Models\Document;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MitraBatchController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
        
        // Melindungi semua method di controller ini
        $this->middleware('auth');
    }

    public function mitraCheckin(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403);
        }

        if ($batch->status !== 'shipped') {
            return back()->with('error', 'Batch tidak dalam status pengiriman.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $result = $this->traceabilityService->processCheckin(
            $batch,
            $user->partner_id,
            $validated,
            $user->id
        );

        if ($result['success']) {
            return back()->with('success', 'Batch berhasil di-check-in.');
        }

        return back()->with('error', $result['message']);
    }

    public function createChild(Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403);
        }

        $validation = $this->traceabilityService->canCreateChild($batch);
        if (!$validation['can']) {
            return back()->with('error', $validation['reason']);
        }

        $productCodes = ProductCode::where('stage', 'MID')->orderBy('code')->get();

        return view('mitra.batches.create-child', compact('batch', 'productCodes'));
    }

    public function storeChild(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_code' => 'required|string|exists:product_codes,code',
            'container_code' => 'required|string|unique:batches,container_code',
            'initial_weight' => 'required|numeric|min:0.1',
            'weight_unit' => 'required|in:kg,ton',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Ubah product_code menjadi product_code_id untuk service
        $productCodeModel = ProductCode::where('code', $validated['product_code'])->first();
        if (!$productCodeModel) {
            return back()->withInput()->with('error', 'Product code tidak valid.');
        }
        
        $serviceData = $validated;
        $serviceData['product_code_id'] = $productCodeModel->id;
        $serviceData['initial_weight'] = $validated['initial_weight']; // Sesuaikan nama kolom jika perlu

        $result = $this->traceabilityService->createChildBatch(
            $batch,
            $serviceData,
            $user->id
        );

        if ($result['success']) {
            return redirect()->route('batches.show', $result['batch'])
                ->with('success', "Child batch {$result['batch']->batch_code} berhasil dibuat.");
        }

        return back()->withInput()->with('error', $result['message']);
    }

    public function mitraCheckout(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403);
        }

        if (!in_array($batch->status, ['ready_to_ship', 'created', 'received'])) {
            return back()->with('error', 'Batch tidak dapat dikirim.');
        }

        $validated = $request->validate([
            'destination_partner_id' => 'required|exists:partners,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $result = $this->traceabilityService->processCheckout($batch, [
            'notes' => $validated['notes'],
        ], $user->id);

        if ($result['success']) {
            $batch->update(['current_owner_partner_id' => $validated['destination_partner_id']]);
            
            return back()->with('success', 'Batch berhasil dikirim ke downstream.');
        }

        return back()->with('error', $result['message']);
    }

    public function uploadDocument(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'type' => 'required|in:lab_certificate,bast,surat_jalan,photo,other',
            'file' => 'required|file|max:5120', // Max 5MB
            'description' => 'nullable|string|max:255',
        ]);

        $filePath = $request->file('file')->store('batch_documents', 'public');

        Document::create([
            'batch_id' => $batch->id,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'file_name' => $request->file('file')->getClientOriginalName(), // Simpan nama asli file
            'uploaded_by_user_id' => auth()->id(),
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Dokumen berhasil diupload.');
    }

    public function downstreamCheckin(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraDownstream()) {
            abort(403);
        }

        if ($batch->status !== 'shipped' || $batch->current_owner_partner_id !== $user->partner_id) {
            return back()->with('error', 'Batch tidak dapat di-check-in.');
        }

        $validated = $request->validate([
            'bast_document' => 'required|file|mimes:pdf,jpg,png|max:5120',
            'signer_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $bastPath = $request->file('bast_document')->store('bast_documents', 'public');

        Document::create([
            'batch_id' => $batch->id,
            'type' => 'bast',
            'file_path' => $bastPath,
            'file_name' => $request->file('bast_document')->getClientOriginalName(),
            'uploaded_by_user_id' => $user->id,
            'description' => "Ditandatangani oleh: {$validated['signer_name']}",
        ]);

        $result = $this->traceabilityService->processCheckin($batch, $user->partner_id, [
            'notes' => $validated['notes'] ?? 'Final delivery ke end-user',
        ], $user->id);

        if ($result['success']) {
            $batch->update(['status' => 'delivered']);
            
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'status_updated',
                'previous_status' => 'received',
                'new_status' => 'delivered',
                'actor_user_id' => $user->id,
                'notes' => 'Batch diterima oleh end-user (Downstream).',
            ]);

            return back()->with('success', 'Batch berhasil diterima. Status: Delivered.');
        }

        return back()->with('error', $result['message']);
    }
}