<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();
        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();

        $eventsQuery = Event::query()
            ->select(['id', 'event_name', 'event_date', 'start_time', 'end_time', 'venue', 'max_slots'])
            ->when($search !== '', function ($query) use ($search) {
                $normalized = mb_strtolower(trim($search));
                $like = '%' . $normalized . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->whereRaw('LOWER(event_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(venue) LIKE ?', [$like]);
                });
            })
            ->withCount('registrations');

        if (strcasecmp($status, 'Open') === 0) {
            $eventsQuery
                ->whereDate('event_date', '>=', $today)
                ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) < events.max_slots');
        }

        if (strcasecmp($status, 'Full') === 0) {
            $eventsQuery
                ->whereDate('event_date', '>=', $today)
                ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) >= events.max_slots');
        }

        if (strcasecmp($status, 'Concluded') === 0) {
            $eventsQuery->whereDate('event_date', '<', $today);
        }

        $events = $eventsQuery
            ->orderBy('event_date')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Event::count(),
            'open' => Event::query()
                ->whereDate('event_date', '>=', $today)
                ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) < events.max_slots')
                ->count(),
            'full' => Event::query()
                ->whereDate('event_date', '>=', $today)
                ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) >= events.max_slots')
                ->count(),
            'past' => Event::query()->whereDate('event_date', '<', $today)->count(),
        ];

        return view('events.index', [
            'events' => $events,
            'status' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function show(Event $event): View
    {
        $event->loadCount('registrations');

        return view('events.show', compact('event'));
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue' => ['required', 'string', 'max:150'],
            'max_slots' => ['required', 'integer', 'min:1'],
        ]);

        $event = Event::create($validated);

        // Invalidate relevant cached pages
        try {
            (new \App\Services\PageCacheService())->purgePattern('/events');
            (new \App\Services\PageCacheService())->purgePattern('/portal');
        } catch (\Throwable $e) {
        }

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $event->loadCount('registrations');

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->loadCount('registrations');

        $rules = [
            'event_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:150'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'max_slots' => ['required', 'integer', 'min:' . max(1, $event->registrations_count)],
        ];

        $validated = $request->validate($rules);

        $event->update($validated);

        try {
            (new \App\Services\PageCacheService())->purgePattern('/events');
            (new \App\Services\PageCacheService())->purgeUrl(route('events.show', ['event' => $event->id]));
            (new \App\Services\PageCacheService())->purgePattern('/portal');
        } catch (\Throwable $e) {
        }

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $showUrl = route('events.show', ['event' => $event->id]);
        $event->delete();

        try {
            (new \App\Services\PageCacheService())->purgePattern('/events');
            (new \App\Services\PageCacheService())->purgeUrl($showUrl);
            (new \App\Services\PageCacheService())->purgePattern('/portal');
        } catch (\Throwable $e) {
        }

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}