<?php

namespace App\Http\Controllers;

use App\Models\CommunityMember;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        // Role is intentionally not accepted from public registration input.
        // The database default assigns every new account the member role.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLogger::record('auth.register', 'Akun pengguna berhasil dibuat.', $user->id);

        return $this->redirectByRole($user);
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            ActivityLogger::record(
                'auth.login.failed',
                'Percobaan autentikasi gagal.'
            );

            return back()
                ->withErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        ActivityLogger::record(
            'auth.login.success',
            'Pengguna berhasil login.',
            (int) Auth::id()
        );

        return $this->redirectByRole(Auth::user());
    }

    private function redirectByRole(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $activeMembership = CommunityMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($activeMembership) {
            return redirect()->route('dashboard');
        }

        $pendingMembership = CommunityMember::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingMembership) {
            return redirect()
                ->route('communities.index')
                ->with('info', 'Pengajuan bergabung kamu sedang menunggu persetujuan Ketua.');
        }

        return redirect()->route('communities.index');
    }

    public function logout(Request $request)
    {
        $userId = (int) $request->user()->id;
        ActivityLogger::record('auth.logout', 'Pengguna berhasil logout.', $userId);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
