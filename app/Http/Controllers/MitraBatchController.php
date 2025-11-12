<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductCode;
use App\Models\Document;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MitraBatchController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
        $this->middleware('auth');
    }

    /**
     * Check-in batch oleh mitra middlestream
     */
    public function mitraCheckin(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403, 'Hanya mitra middlestream yang dapat melakukan check-in.');
        }

        if ($batch->status !== 'shipped') {
            return back()->with('error', 'Batch tidak dalam status pengiriman. Status: ' . $batch->getStatusLabel());
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
            return back()->with('success', 'Batch ' . $batch->batch_code . ' berhasil di-check-in.');
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Form buat batch turunan
     */
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

    /**
     * Simpan batch turunan
     */
    public function storeChild(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_code' => 'required|string|exists:product_codes,code',
            'container_code' => 'required|string|unique:batches,container_code',
            'initial_weight' => 'required|numeric|min:0.01',
            'weight_unit' => 'required|in:kg,ton',
            'notes' => 'nullable|string|max:1000',
            'lab_certificate' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Buat child batch via service
            $result = $this->traceabilityService->createChildBatch(
                $batch,
                $validated,
                $user->id
            );

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $childBatch = $result['batch'];

            // Upload lab certificate jika ada
            if ($request->hasFile('lab_certificate')) {
                $filePath = $request->file('lab_certificate')->store('lab_certificates', 'public');
                
                Document::create([
                    'batch_id' => $childBatch->id,
                    'type' => 'lab_certificate',
                    'file_path' => $filePath,
                    'file_name' => $request->file('lab_certificate')->getClientOriginalName(),
                    'uploaded_by_user_id' => $user->id,
                    'description' => 'Sertifikat lab untuk batch turunan',
                ]);
            }

            DB::commit();

            return redirect()->route('batches.show', $childBatch)
                ->with('success', 'Batch turunan ' . $childBatch->batch_code . ' berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal membuat batch turunan: ' . $e->getMessage());
        }
    }

    /**
     * Check-out batch oleh mitra (kirim ke downstream)
     */
    public function mitraCheckout(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraMiddlestream()) {
            abort(403);
        }

        if (!in_array($batch->status, ['ready_to_ship', 'created', 'received'])) {
            return back()->with('error', 'Batch tidak dapat dikirim. Status: ' . $batch->getStatusLabel());
        }

        $validated = $request->validate([
            'destination_partner_id' => 'required|exists:partners,id',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $result = $this->traceabilityService->processCheckout($batch, [
                'notes' => $validated['notes'],
            ], $user->id);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Update destination partner
            $batch->update(['current_owner_partner_id' => $validated['destination_partner_id']]);

            DB::commit();

            return back()->with('success', 'Batch berhasil dikirim ke downstream.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Upload dokumen pendukung
     */
    public function uploadDocument(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'type' => 'required|in:lab_certificate,bast,surat_jalan,photo,other',
            'file' => 'required|file|max:5120',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $filePath = $request->file('file')->store('batch_documents', 'public');

            Document::create([
                'batch_id' => $batch->id,
                'type' => $validated['type'],
                'file_path' => $filePath,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'uploaded_by_user_id' => auth()->id(),
                'description' => $validated['description'],
            ]);

            return back()->with('success', 'Dokumen berhasil diupload.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Check-in final oleh downstream (penerima akhir)
     */
    public function downstreamCheckin(Request $request, Batch $batch)
    {
        $user = auth()->user();
        
        if (!$user->isMitraDownstream()) {
            abort(403, 'Hanya mitra downstream yang dapat melakukan check-in final.');
        }

        if ($batch->status !== 'shipped') {
            return back()->with('error', 'Batch tidak dalam status pengiriman.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'bast_document' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Mark as delivered (status final)
            $result = $this->traceabilityService->markAsDelivered($batch, $user->id, $validated);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Update owner
            $batch->update(['current_owner_partner_id' => $user->partner_id]);

            // Upload BAST jika ada
            if ($request->hasFile('bast_document')) {
                $filePath = $request->file('bast_document')->store('bast_documents', 'public');
                
                Document::create([
                    'batch_id' => $batch->id,
                    'type' => 'bast',
                    'file_path' => $filePath,
                    'file_name' => $request->file('bast_document')->getClientOriginalName(),
                    'uploaded_by_user_id' => $user->id,
                    'description' => 'BAST penerimaan final',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Batch ' . $batch->batch_code . ' berhasil diterima (delivered).');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}