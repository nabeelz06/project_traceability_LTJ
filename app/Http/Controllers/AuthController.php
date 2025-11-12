<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cek apakah user exists
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar dalam sistem.',
            ])->onlyInput('email');
        }

        // Cek apakah user aktif
        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ])->onlyInput('email');
        }

        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Log login
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'login_time' => now()->toDateTimeString(),
                ],
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'password' => 'Password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Log logout sebelum logout
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'user_logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => [
                'logout_time' => now()->toDateTimeString(),
            ],
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load('partner');

        // Get recent activities
        $recentActivities = \App\Models\BatchLog::where('actor_user_id', $user->id)
            ->with('batch')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get login history
        $loginHistory = SystemLog::where('user_id', $user->id)
            ->whereIn('action', ['user_login', 'user_logout'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('auth.profile', compact('user', 'recentActivities', 'loginHistory'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $user->update($validated);

            // Log aktivitas
            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'profile_updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'updated_fields' => array_keys($validated),
                ],
            ]);

            DB::commit();

            return back()->with('success', 'Profile berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'password_changed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'changed_at' => now()->toDateTimeString(),
                ],
            ]);

            DB::commit();

            return back()->with('success', 'Password berhasil diubah.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }
}