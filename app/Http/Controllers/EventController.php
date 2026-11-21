<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::query()
            ->withCount(['rsvps', 'attendingRsvps'])
            ->orderByDesc('starts_at')
            ->paginate(10);

        $myRsvps = EventRsvp::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('event_id', $events->pluck('id'))
            ->get()
            ->keyBy('event_id');

        return view('events.index', compact('events', 'myRsvps'));
    }

    public function create()
    {
        $this->authorize('create', Event::class);

        return view('events.create');
    }

    public function store(EventRequest $request)
    {
        $this->authorize('create', Event::class);

        $event = new Event($request->validated());
        $event->created_by = $request->user()->id;
        $event->updated_by = $request->user()->id;
        $event->save();

        ActivityLogger::record(
            'event.created',
            'Acara ID '.$event->id.' "'.$event->title.'" dibuat.',
            (int) $request->user()->id
        );

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Acara berhasil dibuat.');
    }

    public function show(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        $event->load(['creator', 'updater']);

        $attendance = $event->rsvps()
            ->with('user')
            ->orderBy('confirmed_at')
            ->orderBy('id')
            ->paginate(25, ['*'], 'attendance_page')
            ->withQueryString();

        $statusCounts = $event->rsvps()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        $myRsvp = $event->rsvpFor($request->user());

        $attendingCount = (int) ($statusCounts[EventRsvp::STATUS_ATTENDING] ?? 0);
        $declinedCount = (int) ($statusCounts[EventRsvp::STATUS_DECLINED] ?? 0);

        return response()
            ->view('events.show', compact(
                'event',
                'attendance',
                'myRsvp',
                'attendingCount',
                'declinedCount'
            ))
            ->header('Cache-Control', 'no-store, private');
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        return view('events.edit', compact('event'));
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->fill($request->validated());
        $event->updated_by = $request->user()->id;
        $event->save();

        ActivityLogger::record(
            'event.updated',
            'Acara ID '.$event->id.' "'.$event->title.'" diperbarui.',
            (int) $request->user()->id
        );

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Acara berhasil diperbarui.');
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorize('delete', $event);

        DB::transaction(function () use ($request, $event) {
            ActivityLogger::record(
                'event.deleted',
                'Acara ID '.$event->id.' "'.$event->title.'" dihapus.',
                (int) $request->user()->id
            );

            $event->delete();
        });

        return redirect()
            ->route('events.index')
            ->with('success', 'Acara berhasil dihapus.');
    }
}
