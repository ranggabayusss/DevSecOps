<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function storeLeaderAccount(Request $request): RedirectResponse
    {
        $request->merge([
            'leader_email' => Str::lower(trim((string) $request->input('leader_email'))),
        ]);

        $validated = $request->validate([
            'leader_name' => ['required', 'string', 'max:100'],
            'leader_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'leader_phone' => ['nullable', 'string', 'max:20'],
            'leader_password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $leader = DB::transaction(function () use ($validated): User {
            $leader = User::create([
                'name' => $validated['leader_name'],
                'email' => $validated['leader_email'],
                'phone' => $validated['leader_phone'] ?? null,
                'password' => Hash::make($validated['leader_password']),
            ]);

            ActivityLogger::record(
                'admin.leader_account.created',
                'Akun calon ketua ID '.$leader->id.' dibuat oleh admin.'
            );

            return $leader;
        });

        return redirect()
            ->route('admin.communities.create')
            ->with('success', 'Akun calon ketua berhasil dibuat. Silakan pilih akun tersebut sebagai Ketua.')
            ->with('created_user_id', $leader->id);
    }
}
