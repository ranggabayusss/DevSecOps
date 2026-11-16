<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityJoinController extends Controller
{
    public function index()
    {
        $communities = Community::with('leader')
            ->withCount('members')
            ->latest()
            ->paginate(15);

        $userMembership = CommunityMember::with('community')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'pending'])
            ->first();

        return view('communities.index', compact('communities', 'userMembership'));
    }

    public function store(Request $request, Community $community)
    {
        $user = $request->user();

        $existingMembership = CommunityMember::where('user_id', $user->id)->first();

        if ($existingMembership && in_array($existingMembership->status, ['active', 'pending'], true)) {
            return back()->with(
                'error',
                'Kamu sudah memiliki keanggotaan aktif atau pengajuan yang sedang diproses.'
            );
        }

        DB::transaction(function () use ($user, $community) {
            // community_members has one row per user; a rejected/inactive
            // membership is safely reused instead of inserting a duplicate.
            CommunityMember::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'community_id' => $community->id,
                    'status' => 'pending',
                    'joined_at' => null,
                ]
            );

            ActivityLogger::record(
                'community.join.requested',
                'Pengajuan bergabung ke komunitas ID '.$community->id.'.',
                (int) $user->id
            );
        });

        return back()->with(
            'success',
            'Pengajuan berhasil dikirim. Menunggu persetujuan Ketua.'
        );
    }
}
