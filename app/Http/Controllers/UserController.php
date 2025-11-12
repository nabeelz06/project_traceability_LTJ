<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partner;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display list users dengan filter
     */
    public function index(Request $request)
    {
        $query = User::with('partner');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by partner
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $partners = Partner::approved()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'partners'));
    }

    /**
     * Show form create user
     */
    public function create()
    {
        $partners = Partner::approved()->orderBy('name')->get();
        return view('admin.users.create', compact('partners'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,operator,mitra_middlestream,mitra_downstream,g_bim,g_esdm',
            'phone' => 'nullable|string|max:20',
            'partner_id' => 'nullable|exists:partners,id',
        ]);

        // Validasi partner_id harus diisi untuk role mitra
        if (in_array($validated['role'], ['mitra_middlestream', 'mitra_downstream'])) {
            if (empty($validated['partner_id'])) {
                return back()->withInput()
                    ->withErrors(['partner_id' => 'Partner harus dipilih untuk role mitra.']);
            }
        } else {
            // Role non-mitra tidak boleh punya partner_id
            $validated['partner_id'] = null;
        }

        DB::beginTransaction();
        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = true;

            $user = User::create($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_created',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User ' . $user->name . ' berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    /**
     * Show detail user
     */
    public function show(User $user)
    {
        $user->load('partner');
        
        // Get recent activities
        $recentActivities = \App\Models\BatchLog::where('actor_user_id', $user->id)
            ->with('batch')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'recentActivities'));
    }

    /**
     * Show form edit user
     */
    public function edit(User $user)
    {
        $partners = Partner::approved()->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'partners'));
    }

    /**
     * Update user data
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:super_admin,admin,operator,mitra_middlestream,mitra_downstream,g_bim,g_esdm',
            'phone' => 'nullable|string|max:20',
            'partner_id' => 'nullable|exists:partners,id',
            'is_active' => 'required|boolean',
        ]);

        // Validasi partner_id untuk role mitra
        if (in_array($validated['role'], ['mitra_middlestream', 'mitra_downstream'])) {
            if (empty($validated['partner_id'])) {
                return back()->withInput()
                    ->withErrors(['partner_id' => 'Partner harus dipilih untuk role mitra.']);
            }
        } else {
            $validated['partner_id'] = null;
        }

        DB::beginTransaction();
        try {
            $user->update($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'Data user berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user)
    {
        // Tidak bisa disable diri sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => $newStatus ? 'user_activated' : 'user_deactivated',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ],
            ]);

            DB::commit();

            $message = $newStatus ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_password_reset',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ],
            ]);

            DB::commit();

            return back()->with('success', 'Password user berhasil direset.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reset password: ' . $e->getMessage());
        }
    }

    /**
     * Delete user (Super Admin only)
     */
    public function destroy(User $user)
    {
        // Tidak bisa hapus diri sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek apakah user masih punya aktivitas
        $hasActivity = \App\Models\BatchLog::where('actor_user_id', $user->id)->exists();
        if ($hasActivity) {
            return back()->with('error', 'User tidak dapat dihapus karena memiliki riwayat aktivitas. Nonaktifkan saja.');
        }

        DB::beginTransaction();
        try {
            $userName = $user->name;
            $user->delete();

            // Log aktivitas
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'user_name' => $userName,
                ],
            ]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User ' . $userName . ' berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}