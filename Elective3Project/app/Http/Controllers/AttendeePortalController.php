<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendeePortalController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $allowedStatuses = ['Open', 'Upcoming'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }
        $today = now()->toDateString();

        $eventsQuery = Event::query()
            ->withCount('registrations')
            ->whereDate('event_date', '>=', $today)
            ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) < events.max_slots');

        if ($search !== '') {
            $normalized = mb_strtolower(trim($search));
            $like = '%' . $normalized . '%';

            $eventsQuery->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(event_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(venue) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
            });
        }

        // The user portal now only surfaces open upcoming events.

        $events = $eventsQuery
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->paginate(6)
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
            'upcoming' => Event::query()->whereDate('event_date', '>=', $today)->count(),
        ];

        return view('portal.home', [
            'events' => $events,
            'search' => $search,
            'status' => $status,
            'stats' => $stats,
        ]);
    }

    public function show(Event $event): View
    {
        $event->loadCount('registrations');

        $mapUrl = 'https://www.google.com/maps?q=' . rawurlencode($event->venue) . '&output=embed';
        $joined = false;

        if (auth()->check()) {
            $joined = Registration::query()
                ->where('event_id', $event->id)
                ->where('email', auth()->user()->email)
                ->exists();
        }

        return view('portal.show', [
            'event' => $event,
            'mapUrl' => $mapUrl,
            'joined' => $joined,
        ]);
    }

    public function join(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if (! $event->canAcceptRegistration()) {
            return back()->withErrors([
                'event' => 'This event is no longer open for registration.',
            ]);
        }

        $alreadyRegistered = Registration::query()
            ->where('event_id', $event->id)
            ->where('email', $user->email)
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('success', 'You are already registered for this event.');
        }

        $names = preg_split('/\s+/', trim($user->full_name), 2);
        $firstName = $names[0] ?? $user->username;
        $lastName = $names[1] ?? $names[0] ?? $user->username;

        Registration::create([
            'event_id' => $event->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'contact_number' => $user->contact_number ?: '00000000000',
            'attendance_status' => 'Pending',
        ]);

        // Create notification for all admins
        $admins = User::query()->where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'event_id' => $event->id,
                'user_id' => $admin->id,
                'type' => 'event_joined',
                'title' => 'New Registration',
                'message' => "{$user->full_name} has joined {$event->event_name}",
            ]);
        }

        return redirect()
            ->route('portal.registrations')
            ->with('success', 'You have joined the event successfully.');
    }

    public function registrations(Request $request): View
    {
        $registrations = Registration::query()
            ->with('event:id,event_name,event_date,venue')
            ->where('email', $request->user()->email)
            ->latest()
            ->paginate(8);

        return view('portal.registrations', compact('registrations'));
    }

    public function quit(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        $registration = Registration::query()
            ->where('event_id', $event->id)
            ->where('email', $user->email)
            ->first();

        if (! $registration) {
            return back()->withErrors([
                'event' => 'You are not registered for this event.',
            ]);
        }

        $registration->delete();

        return redirect()
            ->route('portal.registrations')
            ->with('success', 'You have left the event successfully.');
    }
}
