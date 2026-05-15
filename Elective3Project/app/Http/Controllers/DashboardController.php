<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $stats = Cache::remember('dashboard:stats', 60, function () use ($today): array {
            return [
                'totalEvents' => Event::count(),
                'totalRegistrations' => Registration::count(),
                'fullEvents' => Event::query()
                    ->whereDate('event_date', '>=', $today)
                    ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) >= events.max_slots')
                    ->count(),
                'upcomingEvents' => Event::query()
                    ->whereDate('event_date', '>=', $today)
                    ->count(),
            ];
        });

        $totalEvents = $stats['totalEvents'];
        $totalRegistrations = $stats['totalRegistrations'];
        $fullEvents = $stats['fullEvents'];
        $upcomingEvents = $stats['upcomingEvents'];

        $recentRegistrations = Registration::query()
            ->select(['id', 'event_id', 'first_name', 'last_name', 'attendance_status', 'created_at'])
            ->with('event:id,event_name')
            ->latest()
            ->limit(6)
            ->get();

        $upcomingList = Event::query()
            ->select(['id', 'event_name', 'event_date', 'start_time', 'max_slots'])
            ->withCount('registrations')
            ->whereDate('event_date', '>=', $today)
            ->orderBy('event_date')
            ->limit(5)
            ->get();

        $registrationEvents = Event::query()
            ->select(['id', 'event_name', 'event_date', 'venue', 'max_slots'])
            ->withCount('registrations')
            ->whereDate('event_date', '>=', $today)
            ->orderBy('event_date')
            ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) < events.max_slots')
            ->get();

        return view('dashboard.index', compact(
            'totalEvents',
            'totalRegistrations',
            'fullEvents',
            'upcomingEvents',
            'recentRegistrations',
            'upcomingList',
            'registrationEvents'
        ));
    }
}