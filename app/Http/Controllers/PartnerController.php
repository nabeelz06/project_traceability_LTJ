<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    /**
     * Display list partners dengan filter
     */
    public function index(Request $request)
    {
        $query = Partner::withCount(['users', 'batches']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $partners = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.partners.index', compact('partners'));
    }

    /**
     * Show form create partner
     */
    public function create()
    {
        return view('admin.partners.create');
    }

    /**
     * Store new partner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:middlestream,downstream',
            'address' => 'required|string|max:500',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:20',
            'pic_email' => 'required|email|max:255',
            'allowed_product_codes' => 'nullable|array',
            'verification_doc' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Upload verification doc jika ada
            if ($request->hasFile('verification_doc')) {
                $validated['verification_doc'] = $request->file('verification_doc')
                    ->store('partner_verifications', 'public');
            }

            // Status default pending untuk review
            $validated['status'] = 'pending';

            $partner = Partner::create($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'partner_created',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.partners.index')
                ->with('success', 'Partner ' . $partner->name . ' berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal mendaftar partner: ' . $e->getMessage());
        }
    }

    /**
     * Show detail partner
     */
    public function show(Partner $partner)
    {
        $partner->load(['users', 'batches']);
        
        return view('admin.partners.show', compact('partner'));
    }

    /**
     * Show form edit partner
     */
    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    /**
     * Update partner data
     */
    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:middlestream,downstream',
            'address' => 'required|string|max:500',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:20',
            'pic_email' => 'required|email|max:255',
            'allowed_product_codes' => 'nullable|array',
            'verification_doc' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Upload verification doc baru jika ada
            if ($request->hasFile('verification_doc')) {
                // Hapus file lama
                if ($partner->verification_doc) {
                    Storage::disk('public')->delete($partner->verification_doc);
                }
                $validated['verification_doc'] = $request->file('verification_doc')
                    ->store('partner_verifications', 'public');
            }

            $partner->update($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'partner_updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.partners.show', $partner)
                ->with('success', 'Data partner berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui partner: ' . $e->getMessage());
        }
    }

    /**
     * Approve partner (Super Admin only)
     */
    public function approve(Partner $partner)
    {
        if ($partner->status === 'approved') {
            return back()->with('info', 'Partner sudah dalam status approved.');
        }

        DB::beginTransaction();
        try {
            $partner->update(['status' => 'approved']);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'partner_approved',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                ],
            ]);

            DB::commit();

            return back()->with('success', 'Partner ' . $partner->name . ' telah disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve partner: ' . $e->getMessage());
        }
    }

    /**
     * Reject partner (Super Admin only)
     */
    public function reject(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $partner->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'partner_rejected',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                    'reason' => $validated['rejection_reason'],
                ],
            ]);

            DB::commit();

            return back()->with('success', 'Partner ' . $partner->name . ' telah ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reject partner: ' . $e->getMessage());
        }
    }

    /**
     * Delete partner (Super Admin only)
     */
    public function destroy(Partner $partner)
    {
        // Cek apakah partner masih punya user atau batch aktif
        if ($partner->users()->count() > 0) {
            return back()->with('error', 'Partner tidak dapat dihapus karena masih memiliki user terdaftar.');
        }

        if ($partner->batches()->whereNotIn('status', ['delivered'])->count() > 0) {
            return back()->with('error', 'Partner tidak dapat dihapus karena masih memiliki batch aktif.');
        }

        DB::beginTransaction();
        try {
            $partnerName = $partner->name;

            // Hapus verification doc jika ada
            if ($partner->verification_doc) {
                Storage::disk('public')->delete($partner->verification_doc);
            }

            $partner->delete();

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'partner_deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'partner_name' => $partnerName,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.partners.index')
                ->with('success', 'Partner ' . $partnerName . ' berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus partner: ' . $e->getMessage());
        }
    }
}