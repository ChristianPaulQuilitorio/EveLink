@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>Edit Registration</h1>
        <p>Update attendee details.</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('registrations.update', $registration) }}" class="form-grid two-col">
            @csrf
            @method('PUT')

            <div class="span-2 event-picker" data-searchable-select>
                <label class="span-2">Search Event
                    <input type="search" placeholder="Type event name, venue, or date" data-select-search>
                </label>

                <label class="span-2">Event
                    <select name="event_id" required>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" data-search-text="{{ strtolower($event->event_name . ' ' . $event->venue . ' ' . $event->event_date->format('M d Y')) }}" {{ (int)old('event_id', $registration->event_id) === $event->id ? 'selected' : '' }}>
                                {{ $event->event_name }} ({{ $event->remaining_slots }} left)
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label>First Name
                <input type="text" name="first_name" value="{{ old('first_name', $registration->first_name) }}" required>
            </label>

            <label>Last Name
                <input type="text" name="last_name" value="{{ old('last_name', $registration->last_name) }}" required>
            </label>

            <label>Email Address
                <input type="email" name="email" value="{{ old('email', $registration->email) }}" required>
            </label>

            <label>Contact Number
                <input type="text" name="contact_number" maxlength="11" value="{{ old('contact_number', $registration->contact_number) }}" required>
            </label>

            <div class="row span-2">
                <a class="btn" href="{{ route('registrations.index', ['event_id' => $registration->event_id]) }}">Cancel</a>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
