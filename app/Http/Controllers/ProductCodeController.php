<?php

namespace App\Http\Controllers;

use App\Models\ProductCode;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductCodeController extends Controller
{
    /**
     * Display list product codes
     */
    public function index(Request $request)
    {
        $query = ProductCode::withCount('batches');

        // Filter by stage
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        // Search by code or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $productCodes = $query->orderBy('code')->paginate(20);

        return view('admin.product-codes.index', compact('productCodes'));
    }

    /**
     * Show form create product code
     */
    public function create()
    {
        return view('admin.product-codes.create');
    }

    /**
     * Store new product code
     * Format code: [STAGE]-[MATERIAL]-[SPEC]
     * Contoh: TIM-MON-RAW, MID-ND-OXI99
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'stage' => 'required|in:Upstream,Midstream,Downstream',
            'material' => 'required|string|max:10',
            'spec' => 'required|string|max:20',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'specifications' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Generate code dari stage, material, spec
            // Format: TIM-MON-RAW (stage prefix + material + spec)
            $stagePrefix = $this->getStagePrefix($validated['stage']);
            $code = strtoupper($stagePrefix . '-' . $validated['material'] . '-' . $validated['spec']);

            // Cek apakah code sudah ada
            if (ProductCode::where('code', $code)->exists()) {
                return back()->withInput()
                    ->with('error', 'Product code ' . $code . ' sudah ada. Gunakan kombinasi material dan spec yang berbeda.');
            }

            // Create product code
            $productCode = ProductCode::create([
                'code' => $code,
                'stage' => $validated['stage'],
                'material' => strtoupper($validated['material']),
                'spec' => strtoupper($validated['spec']),
                'description' => $validated['description'],
                'category' => $validated['category'] ?? null,
                'specifications' => $validated['specifications'] ?? null,
            ]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'product_code_created',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'code' => $productCode->code,
                    'description' => $productCode->description,
                ]),
            ]);

            DB::commit();

            return redirect()->route('admin.product-codes.index')
                ->with('success', 'Product code ' . $productCode->code . ' berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menambahkan product code: ' . $e->getMessage());
        }
    }

    /**
     * Show detail product code
     */
    public function show(ProductCode $productCode)
    {
        $productCode->loadCount('batches');

        return view('admin.product-codes.show', compact('productCode'));
    }

    /**
     * Show form edit product code
     */
    public function edit(ProductCode $productCode)
    {
        return view('admin.product-codes.edit', compact('productCode'));
    }

    /**
     * Update product code
     */
    public function update(Request $request, ProductCode $productCode)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'specifications' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $productCode->update($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'product_code_updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'code' => $productCode->code,
                ]),
            ]);

            DB::commit();

            return redirect()->route('admin.product-codes.show', $productCode)
                ->with('success', 'Product code berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui product code: ' . $e->getMessage());
        }
    }

    /**
     * Delete product code
     */
    public function destroy(ProductCode $productCode)
    {
        // Cek apakah product code masih digunakan
        if ($productCode->batches()->count() > 0) {
            return back()->with('error', 'Product code tidak dapat dihapus karena masih digunakan oleh batch.');
        }

        DB::beginTransaction();
        try {
            $code = $productCode->code;
            $productCode->delete();

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'product_code_deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => json_encode([
                    'code' => $code,
                ]),
            ]);

            DB::commit();

            return redirect()->route('admin.product-codes.index')
                ->with('success', 'Product code berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus product code: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Get stage prefix untuk generate code
     * Upstream = TIM, Midstream = MID, Downstream = FINAL
     */
    private function getStagePrefix($stage)
    {
        $prefixes = [
            'Upstream' => 'TIM',
            'Midstream' => 'MID',
            'Downstream' => 'FINAL',
        ];

        return $prefixes[$stage] ?? 'TIM';
    }
}