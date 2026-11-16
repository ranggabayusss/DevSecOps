<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    // Menampilkan anggota komunitas
    public function index(Community $community)
    {
        $community->load('leader');

        $members = $community->members()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('admin.members.index', compact(
            'community',
            'members'
        ));
    }

    // Menampilkan form tambah anggota
    public function create(Community $community)
    {
        $users = User::where('role', 'member')
            ->whereDoesntHave('communityMemberships', function ($query) {
                $query->whereIn('status', ['active', 'pending']);
            })
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email']);

        return view('admin.members.create', compact(
            'community',
            'users'
    ));
    }

    // Menambahkan anggota ke komunitas
    public function store(Request $request, Community $community)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'member'),
            ],
        ]);

        // Periksa apakah pengguna sudah aktif atau memiliki permintaan pending
        $hasMembership = CommunityMember::where('user_id', $validated['user_id'])
            ->whereIn('status', ['active', 'pending'])
            ->exists();

        if ($hasMembership) {
            return back()
                ->withInput()
                ->with('error', 'Pengguna sudah menjadi anggota atau memiliki permintaan bergabung yang belum diproses.');
        }

        // Tambahkan pengguna sebagai anggota aktif
        DB::transaction(function () use ($validated, $community) {
            CommunityMember::updateOrCreate(
                ['user_id' => $validated['user_id']],
                [
                    'community_id' => $community->id,
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

            ActivityLogger::record(
                'community.member.added',
                'Pengguna ID '.$validated['user_id'].' ditambahkan ke komunitas ID '.$community->id.'.'
            );
        });

        return redirect()
            ->route('admin.communities.members.index', $community->id)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }   

    // Mengeluarkan anggota dari komunitas
    public function destroy(Community $community, CommunityMember $membership)
    {
        // Pastikan keanggotaan berasal dari komunitas yang dipilih
        abort_unless(
            (int) $membership->community_id === (int) $community->id,
            404
        );

        // Ketua komunitas tidak boleh dikeluarkan melalui fitur ini
        if ((int) $community->leader_id === (int) $membership->user_id) {
            return back()->with(
                'error',
                'Ketua komunitas tidak dapat dikeluarkan. Tunjuk Ketua pengganti terlebih dahulu.'
            );
        }

        DB::transaction(function () use ($community, $membership) {
            ActivityLogger::record(
                'community.member.removed',
                'Keanggotaan ID '.$membership->id.' dihapus dari komunitas ID '.$community->id.'.'
            );

            $membership->delete();
        });

        return redirect()
            ->route('admin.communities.members.index', $community->id)
            ->with('success', 'Anggota berhasil dikeluarkan dari komunitas.');
    }
}