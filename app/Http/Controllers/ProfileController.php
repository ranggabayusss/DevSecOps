<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Support\ActivityLogger;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => request()->user(),
            'membership' => request()->user()->communityMemberships()->with('community')->first(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->fill($request->validated());
        $user->save();

        ActivityLogger::record(
            'profile.updated',
            'Profil anggota ID '.$user->id.' diperbarui.',
            (int) $user->id
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
