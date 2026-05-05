@extends('layouts.portal')

@section('content')
    <section class="portal-hero card">
        <div class="portal-hero-copy">
            <span class="portal-kicker">Community Matters</span>
            <h1>Explore Community Events Together.</h1>
            <p>Browse open, upcoming, and full events. Open an event to view details, location, and join instantly once you sign in.</p>

            <div class="portal-hero-actions">
                <a class="btn btn-primary" href="#portal-events">Browse Events</a>
                @guest
                    <a class="btn" href="{{ route('portal.register') }}">Join as Attendee</a>
                @endguest
            </div>
        </div>

        <div class="portal-hero-panel">
            <div class="portal-hero-card">
                <strong>{{ $stats['open'] }}</strong>
                <span>Open events now</span>
            </div>
            <div class="portal-hero-card accent">
                <strong>{{ $stats['upcoming'] }}</strong>
                <span>Total upcoming events</span>
            </div>
            <div class="portal-hero-note">
                Register once, join the events you love, and keep your event history in one place.
            </div>
        </div>
    </section>

    <section class="portal-toolbar card">
        <form method="GET" class="portal-search-form">
            <label class="portal-search-field">
                <span class="search-icon" aria-hidden="true">⌕</span>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search events, venues, or descriptions..." oninput="this.form.submit()">
            </label>
        </form>

        <form method="GET" class="portal-filter-form">
            <input type="hidden" name="q" value="{{ $search }}">
            <select name="status" onchange="this.form.submit()">
                <option value="">All Events</option>
                @foreach(['Open', 'Upcoming'] as $option)
                    <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <section id="portal-events" class="portal-section">
        <div class="section-head">
            <div>
                <h2>Available Events</h2>
                <p>Showing {{ $events->count() }} events based on your filters.</p>
            </div>
            <span class="section-sorted">Sorted by: Soonest first</span>
        </div>

        <div class="portal-grid">
            @forelse($events as $event)
                @php
                    $statusClass = strtolower($event->status);
                    $mapQuery = rawurlencode($event->venue);
                @endphp
                <article class="portal-event card">
                    <div class="portal-event-topline">
                        <span class="event-tag">{{ $event->status }}</span>
                        <span class="event-tag muted">{{ $event->remaining_slots }} slots left</span>
                    </div>

                    <div class="portal-event-body">
                        <div class="portal-event-meta">
                            <h3>{{ $event->event_name }}</h3>
                            <div class="portal-event-details">
                                <span>{{ $event->event_date->format('M d, Y') }}</span>
                                <span>{{ $event->time_range }}</span>
                                <span>{{ $event->venue }}</span>
                            </div>
                            <p>{{ $event->description ?: 'A community event open to residents and attendees.' }}</p>
                        </div>

                        <div class="portal-meter">
                            <div>
                                <span>Availability</span>
                                <strong>{{ $event->registered_count }}/{{ $event->max_slots }}</strong>
                            </div>
                            <div class="portal-progress">
                                <span style="width: {{ $event->max_slots > 0 ? min(100, round(($event->registered_count / $event->max_slots) * 100)) : 0 }}%;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="portal-event-actions">
                        <a class="btn" href="{{ route('portal.events.show', $event) }}">View Details</a>
                        @auth
                            @if(auth()->user()->role === 'attendee' && $event->canAcceptRegistration())
                                <form method="POST" action="{{ route('portal.events.join', $event) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Join Event</button>
                                </form>
                            @elseif(auth()->user()->role === 'attendee')
                                <button type="button" class="btn btn-primary" disabled>{{ $event->status }}</button>
                            @else
                                <a class="btn btn-primary" href="{{ route('dashboard') }}">Open Admin Dashboard</a>
                            @endif
                        @else
                            <a class="btn btn-primary" href="{{ route('portal.login') }}">Log in to Join</a>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="portal-empty card">
                    No events match your current filters.
                </div>
            @endforelse
        </div>

        <div class="pagination-footer portal-pagination">
            <div class="pagination-summary">
                Showing <strong>{{ $events->firstItem() ?? 0 }}</strong> to <strong>{{ $events->lastItem() ?? 0 }}</strong> of <strong>{{ $events->total() }}</strong> events
            </div>
            <div class="compact-pagination">
                <a class="page-pill {{ $events->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $events->previousPageUrl() ?? '#' }}">Previous</a>
                <a class="page-pill {{ $events->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $events->nextPageUrl() ?? '#' }}">Next</a>
            </div>
        </div>
    </section>
@endsection
