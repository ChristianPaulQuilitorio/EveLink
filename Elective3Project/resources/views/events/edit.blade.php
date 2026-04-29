@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>Edit Event</h1>
        <p>Update event details and capacity.</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('events.update', $event) }}" class="form-grid two-col">
            @csrf
            @method('PUT')

            <label>Event Name
                <input type="text" name="event_name" value="{{ old('event_name', $event->event_name) }}" required>
            </label>

            <label>Venue
                <input type="text" name="venue" value="{{ old('venue', $event->venue) }}" required>
            </label>

            <label>Date
                <input type="date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required>
            </label>

            <label>Start Time
                <input type="time" name="start_time" value="{{ old('start_time', $event->start_time) }}">
            </label>

            <label>End Time
                <input type="time" name="end_time" value="{{ old('end_time', $event->end_time) }}">
            </label>

            <label>Max Slots
                <input type="number" name="max_slots" min="{{ max(1, $event->registrations_count) }}" value="{{ old('max_slots', $event->max_slots) }}" required>
            </label>

            <label class="span-2">About the Event
                <textarea name="description" rows="5">{{ old('description', $event->description) }}</textarea>
            </label>

            <div class="row span-2">
                <a class="btn" href="{{ route('events.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
