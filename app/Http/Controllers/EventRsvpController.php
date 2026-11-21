<?php

namespace App\Http\Controllers;

use App\Http\Requests\RsvpRequest;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class EventRsvpController extends Controller
{
    public function store(RsvpRequest $request, Event $event)
    {
        $this->authorize('view', $event);

        $user = $request->user();

        if (! $user->hasCompleteMemberProfile()) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Lengkapi nomor anggota, nama motor, dan plat nomor sebelum melakukan RSVP.');
        }

        if ($event->isFinished()) {
            return back()->with('error', 'Konfirmasi kehadiran tidak dapat diubah karena acara sudah selesai.');
        }

        $validated = $request->validated();
        $now = now();

        DB::transaction(function () use ($event, $user, $validated, $now) {
            DB::table('event_rsvps')->insertOrIgnore([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $validated['status'],
                'confirmed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $rsvp = EventRsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $rsvp->status;

            $rsvp->forceFill([
                'status' => $validated['status'],
                'confirmed_at' => $now,
            ])->save();

            ActivityLogger::record(
                'event.rsvp.updated',
                'RSVP acara ID '.$event->id.' diubah dari '.($previousStatus ?? '-')
                    .' menjadi '.$validated['status'].'.',
                (int) $user->id
            );
        }, 3);

        return back()->with(
            'success',
            'Konfirmasi kehadiran berhasil disimpan: '.EventRsvp::statusLabels()[$validated['status']].'.'
        );
    }
}
