<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductCode;
use App\Models\Partner;
use App\Models\BatchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BatchController extends Controller
{
    /**
     * Tampilkan daftar semua batch
     */
    public function index(Request $request)
    {
        $query = Batch::with(['productCode', 'creator', 'currentPartner']);

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan product code
        if ($request->has('product_code_id') && $request->product_code_id != '') {
            $query->where('product_code_id', $request->product_code_id);
        }

        // Pencarian
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Parent batch only atau semua
        if ($request->has('type') && $request->type == 'parent') {
            $query->parentOnly();
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // FIX: Gunakan lowercase untuk konsistensi dengan view
        $productcodes = ProductCode::where('stage', 'Mitra Pemurnian LTJ')->get();

        return view('batches.index', compact('batches', 'productcodes'));
    }

    /**
     * Form create batch baru
     */
    public function create()
    {
        // Hanya Super Admin dan Admin PT Timah yang bisa create parent batch
        if (!Auth::user()->canCreateParentBatch()) {
            abort(403, 'Anda tidak memiliki akses untuk membuat batch induk.');
        }

        $productCodes = ProductCode::where('stage', 'Mitra Pemurnian LTJ')
            ->orderBy('code')
            ->get();

        return view('batches.create', compact('productCodes'));
    }

    /**
     * Store batch baru ke database dengan evidence upload
     */
    public function store(Request $request)
    {
        // Validasi permission
        if (!Auth::user()->canCreateParentBatch()) {
            abort(403, 'Anda tidak memiliki akses untuk membuat batch induk.');
        }

        // Validasi input
        $validated = $request->validate([
            'product_code_id' => 'required|exists:product_codes,id',
            'initial_weight' => 'required|numeric|min:0.01',
            'weight_unit' => 'required|in:kg,ton',
            'tonase' => 'nullable|numeric|min:0',
            'konsentrat_persen' => 'required|numeric|min:0|max:100',
            'container_code' => 'nullable|string|max:50',
            'origin_location' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            
            // 5 Unsur LTJ
            'nd_content' => 'nullable|numeric|min:0|max:100',
            'y_content' => 'nullable|numeric|min:0|max:100',
            'ce_content' => 'nullable|numeric|min:0|max:100',
            'la_content' => 'nullable|numeric|min:0|max:100',
            'pr_content' => 'nullable|numeric|min:0|max:100',
            
            // Massa LTJ
            'massa_ltj_kg' => 'nullable|numeric|min:0',
            
            // GPS Coordinates
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'current_location_name' => 'nullable|string|max:255',
            
            // Evidence Files
            'evidence_photos.*' => 'nullable|image|max:5120', // 5MB per foto
            'evidence_videos.*' => 'nullable|mimes:mp4,avi,mov|max:51200', // 50MB per video
            'evidence_documents.*' => 'nullable|mimes:pdf,doc,docx,xlsx|max:10240', // 10MB per dokumen
        ], [
            'product_code_id.required' => 'Product code wajib dipilih.',
            'initial_weight.required' => 'Tonase/berat wajib diisi.',
            'initial_weight.min' => 'Tonase/berat minimal 0.01.',
            'weight_unit.required' => 'Unit wajib dipilih.',
            'konsentrat_persen.required' => 'Konsentrat wajib diisi.',
            'konsentrat_persen.max' => 'Konsentrat maksimal 100%.',
            'origin_location.required' => 'Lokasi asal wajib diisi.',
            'evidence_photos.*.image' => 'File harus berupa gambar.',
            'evidence_photos.*.max' => 'Ukuran foto maksimal 5MB.',
            'evidence_videos.*.mimes' => 'Format video harus mp4, avi, atau mov.',
            'evidence_videos.*.max' => 'Ukuran video maksimal 50MB.',
            'evidence_documents.*.mimes' => 'Format dokumen harus pdf, doc, docx, atau xlsx.',
            'evidence_documents.*.max' => 'Ukuran dokumen maksimal 10MB.',
        ]);

        // Validasi tambahan: Total 5 unsur tidak boleh > 100%
        $totalUnsur = ($validated['nd_content'] ?? 0) + 
                     ($validated['y_content'] ?? 0) + 
                     ($validated['ce_content'] ?? 0) + 
                     ($validated['la_content'] ?? 0) + 
                     ($validated['pr_content'] ?? 0);

        if ($totalUnsur > 100) {
            return back()
                ->withErrors(['nd_content' => 'Total kandungan 5 unsur LTJ tidak boleh melebihi 100%.'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Konversi tonase untuk initial_weight dan current_weight
            $weightInKg = $validated['weight_unit'] == 'ton' 
                ? $validated['initial_weight'] * 1000 
                : $validated['initial_weight'];

            // Hitung tonase dalam ton
            $tonaseInTon = $validated['weight_unit'] == 'ton' 
                ? $validated['initial_weight'] 
                : $validated['initial_weight'] / 1000;

            // Auto-calculate massa_ltj_kg jika tidak diisi manual
            $massaLtjKg = $validated['massa_ltj_kg'] ?? 
                         Batch::calculateMassaLtj($tonaseInTon, $validated['konsentrat_persen']);

            // Generate container code jika kosong
            $containerCode = $validated['container_code'];
            if (empty($containerCode)) {
                $containerCode = 'K-TMH-' . str_pad(Batch::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            // Handle Evidence File Uploads
            $evidencePhotos = [];
            $evidenceVideos = [];
            $evidenceDocuments = [];

            // Upload photos
            if ($request->hasFile('evidence_photos')) {
                foreach ($request->file('evidence_photos') as $photo) {
                    $path = $photo->store('evidence/photos', 'public');
                    $evidencePhotos[] = [
                        'url' => Storage::url($path),
                        'filename' => $photo->getClientOriginalName(),
                        'size' => $photo->getSize(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }

            // Upload videos
            if ($request->hasFile('evidence_videos')) {
                foreach ($request->file('evidence_videos') as $video) {
                    $path = $video->store('evidence/videos', 'public');
                    $evidenceVideos[] = [
                        'url' => Storage::url($path),
                        'filename' => $video->getClientOriginalName(),
                        'size' => $video->getSize(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }

            // Upload documents
            if ($request->hasFile('evidence_documents')) {
                foreach ($request->file('evidence_documents') as $document) {
                    $path = $document->store('evidence/documents', 'public');
                    $evidenceDocuments[] = [
                        'url' => Storage::url($path),
                        'filename' => $document->getClientOriginalName(),
                        'size' => $document->getSize(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }

            // Buat batch baru
            $batch = Batch::create([
                'product_code_id' => $validated['product_code_id'],
                'initial_weight' => $weightInKg,
                'current_weight' => $weightInKg,
                'weight_unit' => 'kg',
                'tonase' => $tonaseInTon,
                'konsentrat_persen' => $validated['konsentrat_persen'],
                'massa_ltj_kg' => $massaLtjKg,
                'container_code' => $containerCode,
                'origin_location' => $validated['origin_location'],
                'current_location' => $validated['origin_location'],
                'keterangan' => $validated['keterangan'],
                
                // 5 Unsur LTJ
                'nd_content' => $validated['nd_content'] ?? null,
                'y_content' => $validated['y_content'] ?? null,
                'ce_content' => $validated['ce_content'] ?? null,
                'la_content' => $validated['la_content'] ?? null,
                'pr_content' => $validated['pr_content'] ?? null,
                
                // GPS Coordinates
                'current_latitude' => $validated['current_latitude'] ?? null,
                'current_longitude' => $validated['current_longitude'] ?? null,
                'current_location_name' => $validated['current_location_name'] ?? $validated['origin_location'],
                'last_gps_update' => ($validated['current_latitude'] && $validated['current_longitude']) ? now() : null,
                
                // Evidence
                'evidence_photos' => !empty($evidencePhotos) ? $evidencePhotos : null,
                'evidence_videos' => !empty($evidenceVideos) ? $evidenceVideos : null,
                'evidence_documents' => !empty($evidenceDocuments) ? $evidenceDocuments : null,
                
                // Status & ownership
                'status' => 'created',
                'created_by' => Auth::id(),
                'current_owner_partner_id' => Auth::user()->partner_id,
                'is_ready' => false,
            ]);

            // Buat log aktivitas
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'BATCH_CREATED',
                'actor_user_id' => Auth::id(),
                'notes' => 'Batch induk dibuat oleh ' . Auth::user()->name,
                'metadata' => json_encode([
                    'batch_code' => $batch->batch_code,
                    'tonase' => $tonaseInTon . ' ton',
                    'konsentrat' => $validated['konsentrat_persen'] . '%',
                    'massa_ltj' => $massaLtjKg . ' kg',
                    'evidence_count' => count($evidencePhotos) + count($evidenceVideos) + count($evidenceDocuments),
                    'has_gps' => $batch->hasGpsCoordinates(),
                ]),
            ]);

            DB::commit();

            return redirect()
                ->route('batches.show', $batch->id)
                ->with('success', 'Batch berhasil dibuat dengan kode: ' . $batch->batch_code);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file yang sudah diupload jika terjadi error
            foreach ($evidencePhotos as $photo) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $photo['url']));
            }
            foreach ($evidenceVideos as $video) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $video['url']));
            }
            foreach ($evidenceDocuments as $doc) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $doc['url']));
            }
            
            return back()
                ->withErrors(['error' => 'Gagal membuat batch: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Tampilkan detail batch
     */
    public function show($id)
    {
        $batch = Batch::with([
            'productCode', 
            'creator', 
            'currentPartner', 
            'parentBatch',
            'childBatches.productCode',
            'logs.actor',
            'documents',
            'shipments'
        ])->findOrFail($id);

        return view('batches.show', compact('batch'));
    }

    /**
     * Form edit batch
     */
    public function edit($id)
    {
        $batch = Batch::findOrFail($id);

        // Cek permission
        if (!$batch->canBeEdited()) {
            return redirect()
                ->route('batches.show', $batch->id)
                ->with('error', 'Batch dengan status "' . $batch->getStatusLabel() . '" tidak dapat diedit.');
        }

        $productCodes = ProductCode::where('stage', 'Mitra Pemurnian LTJ')
            ->orderBy('code')
            ->get();

        return view('batches.edit', compact('batch', 'productCodes'));
    }

    /**
     * Update batch
     */
    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        // Cek permission
        if (!$batch->canBeEdited()) {
            return redirect()
                ->route('batches.show', $batch->id)
                ->with('error', 'Batch dengan status "' . $batch->getStatusLabel() . '" tidak dapat diedit.');
        }

        // Validasi sama seperti store
        $validated = $request->validate([
            'product_code_id' => 'required|exists:product_codes,id',
            'initial_weight' => 'required|numeric|min:0.01',
            'weight_unit' => 'required|in:kg,ton',
            'tonase' => 'nullable|numeric|min:0',
            'konsentrat_persen' => 'required|numeric|min:0|max:100',
            'container_code' => 'nullable|string|max:50',
            'origin_location' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'nd_content' => 'nullable|numeric|min:0|max:100',
            'y_content' => 'nullable|numeric|min:0|max:100',
            'ce_content' => 'nullable|numeric|min:0|max:100',
            'la_content' => 'nullable|numeric|min:0|max:100',
            'pr_content' => 'nullable|numeric|min:0|max:100',
            'massa_ltj_kg' => 'nullable|numeric|min:0',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'current_location_name' => 'nullable|string|max:255',
        ]);

        // Validasi total unsur
        $totalUnsur = ($validated['nd_content'] ?? 0) + 
                     ($validated['y_content'] ?? 0) + 
                     ($validated['ce_content'] ?? 0) + 
                     ($validated['la_content'] ?? 0) + 
                     ($validated['pr_content'] ?? 0);

        if ($totalUnsur > 100) {
            return back()
                ->withErrors(['nd_content' => 'Total kandungan 5 unsur LTJ tidak boleh melebihi 100%.'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Konversi weight
            $weightInKg = $validated['weight_unit'] == 'ton' 
                ? $validated['initial_weight'] * 1000 
                : $validated['initial_weight'];

            $tonaseInTon = $validated['weight_unit'] == 'ton' 
                ? $validated['initial_weight'] 
                : $validated['initial_weight'] / 1000;

            // Recalculate massa_ltj_kg
            $massaLtjKg = $validated['massa_ltj_kg'] ?? 
                         Batch::calculateMassaLtj($tonaseInTon, $validated['konsentrat_persen']);

            // Update batch
            $batch->update([
                'product_code_id' => $validated['product_code_id'],
                'initial_weight' => $weightInKg,
                'current_weight' => $weightInKg,
                'tonase' => $tonaseInTon,
                'konsentrat_persen' => $validated['konsentrat_persen'],
                'massa_ltj_kg' => $massaLtjKg,
                'container_code' => $validated['container_code'],
                'origin_location' => $validated['origin_location'],
                'keterangan' => $validated['keterangan'],
                'nd_content' => $validated['nd_content'] ?? null,
                'y_content' => $validated['y_content'] ?? null,
                'ce_content' => $validated['ce_content'] ?? null,
                'la_content' => $validated['la_content'] ?? null,
                'pr_content' => $validated['pr_content'] ?? null,
                'current_latitude' => $validated['current_latitude'] ?? $batch->current_latitude,
                'current_longitude' => $validated['current_longitude'] ?? $batch->current_longitude,
                'current_location_name' => $validated['current_location_name'] ?? $batch->current_location_name,
                'last_gps_update' => ($validated['current_latitude'] && $validated['current_longitude']) ? now() : $batch->last_gps_update,
            ]);

            // Log aktivitas
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'BATCH_UPDATED',
                'actor_user_id' => Auth::id(),
                'notes' => 'Batch diupdate oleh ' . Auth::user()->name,
            ]);

            DB::commit();

            return redirect()
                ->route('batches.show', $batch->id)
                ->with('success', 'Batch berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withErrors(['error' => 'Gagal update batch: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Hapus batch
     */
    public function destroy($id)
    {
        $batch = Batch::findOrFail($id);

        if (!$batch->canBeDeleted()) {
            return redirect()
                ->route('batches.index')
                ->with('error', 'Batch tidak dapat dihapus karena sudah memiliki child batch atau status tidak sesuai.');
        }

        try {
            DB::beginTransaction();

            // Hapus evidence files dari storage
            if ($batch->evidence_photos) {
                foreach ($batch->evidence_photos as $photo) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $photo['url']));
                }
            }
            if ($batch->evidence_videos) {
                foreach ($batch->evidence_videos as $video) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $video['url']));
                }
            }
            if ($batch->evidence_documents) {
                foreach ($batch->evidence_documents as $doc) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $doc['url']));
                }
            }

            // Hapus logs terkait
            $batch->logs()->delete();

            // Hapus batch
            $batch->delete();

            DB::commit();

            return redirect()
                ->route('batches.index')
                ->with('success', 'Batch berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('batches.index')
                ->with('error', 'Gagal menghapus batch: ' . $e->getMessage());
        }
    }
}