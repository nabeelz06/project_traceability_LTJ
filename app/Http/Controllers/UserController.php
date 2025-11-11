<?php

/**
 * File: app/Http/Controllers/UserController.php
 * Controller untuk manajemen user (Super Admin only)
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partner;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display list of users
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

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('nomor_pegawai', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $partners = Partner::approved()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'partners'));
    }

    /**
     * Show create user form
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
            'username' => 'nullable|string|unique:users,username',
            'role' => 'required|in:super_admin,admin,operator,mitra_middlestream,mitra_downstream,auditor',
            'nomor_pegawai' => 'required_if:role,super_admin,admin,operator',
            'partner_id' => 'required_if:role,mitra_middlestream,mitra_downstream',
            'phone' => 'nullable|string|max:20',
            'verification_doc' => 'nullable|file|mimes:pdf,png,jpg|max:2048',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'enable_2fa' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('verification_doc')) {
            $validated['verification_doc'] = $request->file('verification_doc')
                ->store('verification_docs', 'public');
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        // Create user
        $user = User::create($validated);

        // Log activity
        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_created',
            'details' => "User baru dibuat: {$user->name} ({$user->email})",
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil dibuat. Email aktivasi telah dikirim.");
    }

    /**
     * Show user detail
     */
    public function show(User $user)
    {
        $user->load(['partner', 'createdBatches', 'batchLogs']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show edit user form
     */
    public function edit(User $user)
    {
        $partners = Partner::approved()->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'partners'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'nullable|string|unique:users,username,' . $user->id,
            'nomor_pegawai' => 'required_if:role,super_admin,admin,operator',
            'partner_id' => 'required_if:role,mitra_middlestream,mitra_downstream',
            'phone' => 'nullable|string|max:20',
            'verification_doc' => 'nullable|file|mimes:pdf,png,jpg|max:2048',
            'enable_2fa' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('verification_doc')) {
            // Delete old file
            if ($user->verification_doc) {
                Storage::disk('public')->delete($user->verification_doc);
            }
            $validated['verification_doc'] = $request->file('verification_doc')
                ->store('verification_docs', 'public');
        }

        $user->update($validated);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_updated',
            'details' => "User diupdate: {$user->name}",
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate.');
    }

    /**
     * Delete user (soft delete)
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_deleted',
            'details' => "User dihapus: {$user->name}",
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_status_changed',
            'details' => "User {$status}: {$user->name}",
        ]);

        return back()->with('success', "User berhasil {$status}.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_password_reset',
            'details' => "Password direset untuk user: {$user->name}",
        ]);

        return back()->with('success', 'Password berhasil direset.');
    }
}