@extends('layouts.app')

@section('content')
    <div class="page-header with-actions">
        <div>
            <h1>{{ $event->event_name }}</h1>
            <p>View event details, capacity, and scheduling information.</p>
        </div>
        <div class="row">
            <a class="btn" href="{{ route('events.index') }}">Back to Events</a>
            <a class="btn btn-primary" href="{{ route('events.edit', $event) }}">Edit Event</a>
        </div>
    </div>

    <div class="cards two">
        <div class="card">
            <h3>Event Information</h3>
            <div class="list">
                <li><strong>Event ID:</strong> EVT-{{ str_pad((string) $event->id, 4, '0', STR_PAD_LEFT) }}</li>
                <li><strong>Date:</strong> {{ $event->event_date->format('F d, Y') }}</li>
                <li><strong>Time:</strong> {{ $event->start_time ?: '--:--' }} - {{ $event->end_time ?: '--:--' }}</li>
                <li><strong>Venue:</strong> {{ $event->venue }}</li>
                <li><strong>Status:</strong> <span class="badge {{ strtolower($event->status) }}">{{ $event->status }}</span></li>
            </div>
        </div>

        <div class="card">
            <h3>Capacity</h3>
            <div class="stat" style="padding:0;">
                <span>Registered Attendees</span>
                <strong>{{ $event->registered_count }}</strong>
            </div>
            <div class="stat" style="padding:0; margin-top:14px;">
                <span>Remaining Slots</span>
                <strong>{{ $event->remaining_slots }}</strong>
            </div>
            <p class="helper" style="margin-top:14px;">{{ $event->description ?: 'No event description provided.' }}</p>
        </div>
    </div>
@endsection
