@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>Add New Event</h1>
        <p>Fill in details to schedule a community event.</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('events.store') }}" class="form-grid two-col">
            @csrf
            <label>Event Name
                <input type="text" name="event_name" value="{{ old('event_name') }}" required>
            </label>

            <label>Venue
                <input type="text" name="venue" value="{{ old('venue') }}" required>
            </label>

            <label>Date
                <input type="date" name="event_date" value="{{ old('event_date') }}" required>
            </label>

            <label>Start Time
                <input type="time" name="start_time" value="{{ old('start_time') }}">
            </label>

            <label>End Time
                <input type="time" name="end_time" value="{{ old('end_time') }}">
            </label>

            <label>Max Slots
                <input type="number" name="max_slots" value="{{ old('max_slots') }}" min="1" required>
            </label>

            <label class="span-2">About the Event
                <textarea name="description" rows="5">{{ old('description') }}</textarea>
            </label>

            <div class="row span-2">
                <a class="btn" href="{{ route('events.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit">Create Event</button>
            </div>
        </form>
    </div>
@endsection
