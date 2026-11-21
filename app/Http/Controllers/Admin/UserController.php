<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMemberRoleRequest;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);

        $users = User::query()
            ->with(['communityMemberships.community'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('member_number', 'like', $like)
                        ->orWhere('license_plate', 'like', $like);
                });
            })
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function updateRole(UpdateMemberRoleRequest $request, User $user)
    {
        $actor = $request->user();

        if ((int) $user->id === (int) $actor->id) {
            return back()->with('error', 'Admin tidak dapat mengubah role akunnya sendiri.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($actor, $user, $validated) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $previousRole = $lockedUser->role;

            if ($previousRole === $validated['role']) {
                return;
            }

            $lockedUser->role = $validated['role'];
            $lockedUser->save();

            ActivityLogger::record(
                'member.role.changed',
                'Role anggota '.($lockedUser->member_number ?? ('ID '.$lockedUser->id))
                    .' diubah dari '.$previousRole.' menjadi '.$validated['role'].'.',
                (int) $actor->id
            );
        });

        return back()->with('success', 'Role anggota berhasil diperbarui.');
    }
}
