@extends('layouts.portal')

@section('content')
    <section class="portal-detail-grid">
        <div class="portal-detail-main card">
            <div class="portal-detail-hero">
                <div class="portal-detail-copy">
                    <span class="event-tag">{{ $event->status }}</span>
                    <h1>{{ $event->event_name }}</h1>
                    <p>{{ $event->description ?: 'This event is open to community attendees.' }}</p>
                </div>

                <div class="portal-detail-meta">
                    <div>
                        <span>Date</span>
                        <strong>{{ $event->event_date->format('F d, Y') }}</strong>
                    </div>
                    <div>
                        <span>Time</span>
                        <strong>{{ $event->time_range }}</strong>
                    </div>
                    <div>
                        <span>Venue</span>
                        <strong>{{ $event->venue }}</strong>
                    </div>
                </div>
            </div>

            <div class="portal-detail-info card-inline-surface">
                <div>
                    <span>Registered Attendees</span>
                    <strong>{{ $event->registered_count }}</strong>
                </div>
                <div>
                    <span>Remaining Slots</span>
                    <strong>{{ $event->remaining_slots }}</strong>
                </div>
                <div>
                    <span>Capacity</span>
                    <strong>{{ $event->max_slots }}</strong>
                </div>
            </div>

            <div class="portal-map card-inline-surface">
                <div class="section-head compact">
                    <div>
                        <h2>Event Location</h2>
                        <p>Geographic map preview for the selected venue.</p>
                    </div>
                    <a class="btn" href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($event->venue) }}" target="_blank" rel="noreferrer">Open in Maps</a>
                </div>
                <iframe
                    title="Event map for {{ $event->venue }}"
                    src="{{ $mapUrl }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="portal-map-frame">
                </iframe>
            </div>
        </div>

        <aside class="portal-detail-side card">
            <div class="portal-registration-box">
                <h2>Join This Event</h2>
                <p>Secure your spot with your attendee account.</p>

                @auth
                    @if(auth()->user()->role === 'attendee')
                        @if($joined)
                            <div class="portal-joined">You are already registered for this event.</div>
                            <form method="POST" action="{{ route('portal.events.quit', $event) }}" class="portal-join-form" onsubmit="return confirm('Are you sure you want to leave this event?');">
                                @csrf
                                <button type="submit" class="btn btn-danger">Leave Event</button>
                            </form>
                        @elseif($event->canAcceptRegistration())
                            <form method="POST" action="{{ route('portal.events.join', $event) }}" class="portal-join-form">
                                @csrf
                                <button type="submit" class="btn btn-primary">Join Event Now</button>
                            </form>
                        @else
                            <div class="portal-joined danger">This event is not open for registration.</div>
                        @endif
                    @else
                        <div class="portal-guest-note">Admin users manage this event from the dashboard.</div>
                        <a class="btn btn-primary" href="{{ route('dashboard') }}">Open Admin Dashboard</a>
                    @endif
                @else
                    <div class="portal-guest-note">Log in or create an attendee account to reserve your spot.</div>
                    <div class="row">
                        <a class="btn btn-primary" href="{{ route('portal.login') }}">Log In</a>
                        <a class="btn" href="{{ route('portal.register') }}">Create Account</a>
                    </div>
                @endauth
            </div>

            <div class="portal-detail-sidebar card-inline-surface">
                <h3>What to Expect</h3>
                <ul class="portal-list">
                    <li>Simple attendee registration flow</li>
                    <li>Live capacity status and availability</li>
                    <li>Location-aware map preview</li>
                    <li>Fast join action for signed-in users</li>
                </ul>
            </div>
        </aside>
    </section>
@endsection
