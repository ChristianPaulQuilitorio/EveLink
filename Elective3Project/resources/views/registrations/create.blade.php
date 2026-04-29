@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>Register New Attendee</h1>
        <p>Create a registration for an event.</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('registrations.store') }}" class="form-grid two-col">
            @csrf

            <div class="span-2 event-picker" data-searchable-select>
                <label class="span-2">Search Event
                    <input type="search" placeholder="Type event name, venue, or date" data-select-search>
                </label>

                <label class="span-2">Event
                    <select name="event_id" required>
                        <option value="">Select Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" data-search-text="{{ strtolower($event->event_name . ' ' . $event->venue . ' ' . $event->event_date->format('M d Y')) }}" {{ (int)old('event_id') === $event->id ? 'selected' : '' }}>
                                {{ $event->event_name }} ({{ $event->remaining_slots }} left)
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label>First Name
                <input type="text" name="first_name" value="{{ old('first_name') }}" required>
            </label>

            <label>Last Name
                <input type="text" name="last_name" value="{{ old('last_name') }}" required>
            </label>

            <label>Email Address
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <label>Contact Number
                <input type="text" name="contact_number" maxlength="11" value="{{ old('contact_number') }}" required>
            </label>

            <div class="row span-2">
                <a class="btn" href="{{ route('registrations.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit">Save Registration</button>
            </div>
        </form>
    </div>
@endsection
