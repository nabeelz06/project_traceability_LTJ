<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;

class TraceabilityController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    /**
     * Halaman pencarian traceability
     */
    public function search(Request $request)
    {
        $batches = null;
        $searchTerm = $request->input('search');

        if ($searchTerm) {
            $batches = Batch::with(['productCode', 'currentPartner', 'creator'])
                ->search($searchTerm)
                ->limit(50)
                ->get();
        }

        return view('traceability.search', compact('batches', 'searchTerm'));
    }

    /**
     * Tampilkan full traceability tree untuk batch
     */
    public function tree(Batch $batch)
    {
        $tree = $this->traceabilityService->buildFullTree($batch);

        return view('traceability.tree', compact('batch', 'tree'));
    }

    /**
     * API endpoint untuk get tree (untuk AJAX)
     */
    public function getTree(Batch $batch)
    {
        $tree = $this->traceabilityService->buildFullTree($batch);

        return response()->json([
            'success' => true,
            'tree' => $tree,
        ]);
    }

    /**
     * API endpoint untuk get chain (linear history)
     */
    public function getChain(Batch $batch)
    {
        $chain = $this->traceabilityService->getFullChain($batch);

        return response()->json([
            'success' => true,
            'chain' => $chain,
        ]);
    }
}