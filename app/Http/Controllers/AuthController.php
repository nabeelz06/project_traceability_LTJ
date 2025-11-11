<?php

/**
 * File: app/Http/Controllers/AuthController.php
 * Controller untuk autentikasi dan manajemen profil
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_id' => ['nullable', 'string'], // Untuk operator handheld
        ]);

        // Cek apakah user exists dan active
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar.',
            ])->onlyInput('email');
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ])->onlyInput('email');
        }

        // Attempt login
        $remember = $request->boolean('remember');
        
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            // Update last login
            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'device_id' => $request->device_id ?? $user->device_id,
            ]);

            // Log aktivitas
            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => ['device_id' => $request->device_id],
            ]);

            // Redirect berdasarkan role
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Log aktivitas
        if (Auth::check()) {
            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Tampilkan halaman forgot password
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim reset password link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link reset password telah dikirim ke email Anda.')
            : back()->withErrors(['email' => 'Gagal mengirim link reset password.']);
    }

    /**
     * Tampilkan halaman reset password
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Proses reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password berhasil direset.')
            : back()->withErrors(['email' => 'Gagal reset password.']);
    }

    /**
     * Tampilkan halaman profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update profile user
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diupdate.');
    }

    /**
     * Update password user
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password berhasil diupdate.');
    }

    /**
     * Helper: Redirect berdasarkan role
     */
    private function redirectBasedOnRole($user)
    {
        return match($user->role) {
            'super_admin', 'admin' => redirect()->route('dashboard'),
            'operator' => redirect()->route('scan.index'),
            'mitra_middlestream' => redirect()->route('mitra.dashboard'),
            'mitra_downstream' => redirect()->route('downstream.dashboard'),
            'auditor' => redirect()->route('audit.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}