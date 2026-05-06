@extends('layouts.app')

@section('content')
    @php
        $selectedEvent = $selectedEvent ?? null;
        $selectedEventTitle = $selectedEvent?->event_name ?? 'No event selected';
        $selectedEventSubtitle = $selectedEvent
            ? $selectedEvent->event_date->format('M d, Y') . ' • ' . $selectedEvent->venue
            : 'Choose an event to manage registrations.';
    @endphp

    <div class="page-header with-actions registrations-header">
        <div>
            <h1>Attendee Registration</h1>
            <p>Manage sign-ups and participant details for your community events.</p>
        </div>
    </div>

    <div class="registrations-shell">
        <div class="card event-context-card">
            <div class="context-label">
                <span class="context-icon">📅</span>
                <span>Active Event Context</span>
            </div>
            <form method="GET" class="context-select-form event-picker">
                <input type="hidden" name="q" value="{{ $search }}">
                <label class="picker-search-label">
                    <span class="picker-label">Search Event</span>
                    <input type="search" name="event_search" value="{{ $eventSearch ?? '' }}" placeholder="Type event title or venue..." oninput="this.form.submit()">
                </label>
                <label class="picker-label-group">
                    <span class="picker-label">Event</span>
                    <select name="event_id" onchange="this.form.submit()" {{ $events->isEmpty() ? 'disabled' : '' }}>
                        @forelse($events as $event)
                            <option value="{{ $event->id }}" {{ (int) $selectedEventId === $event->id ? 'selected' : '' }}>
                                {{ $event->event_name }}
                            </option>
                        @empty
                            <option value="">No matching events</option>
                        @endforelse
                    </select>
                </label>
            </form>
        </div>

        <div class="card capacity-card">
            <div class="capacity-meta">
                <div class="capacity-stat">
                    <div class="capacity-icon">👥</div>
                    <div>
                        <span>Capacity Status</span>
                        <strong>{{ $selectedEvent?->registered_count ?? 0 }} / {{ $selectedEvent?->max_slots ?? 0 }}</strong>
                        <small>Slots</small>
                    </div>
                </div>
                <div class="capacity-utilization">
                    <span>Current Utilization</span>
                    <strong>{{ $capacityPercent }}% Full</strong>
                </div>
            </div>
            <div class="capacity-meter" aria-hidden="true">
                <span style="width: {{ $capacityPercent }}%;"></span>
            </div>
        </div>

        <div class="registrations-grid">
            <div class="card form-panel">
                <div class="card-head">
                    <div>
                        <h3>Registration Form</h3>
                        <p>Enter the participant's details below.</p>
                    </div>
                </div>

                <div class="selected-event-summary">
                    <strong>{{ $selectedEventTitle }}</strong>
                    <span>{{ $selectedEventSubtitle }}</span>
                </div>

                <form method="POST" action="{{ route('registrations.store') }}" class="form-grid registration-form">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $selectedEventId }}">

                    <label>First Name
                        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="John" required>
                    </label>

                    <label>Last Name
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required>
                    </label>

                    <label class="span-2">Email Address
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="john.doe@example.com" required>
                    </label>

                    <label class="span-2">Contact Number
                        <input type="text" name="contact_number" maxlength="11" value="{{ old('contact_number') }}" placeholder="09123456789" required>
                    </label>

                    <button class="btn btn-primary span-2 registration-submit" type="submit" {{ $selectedEvent ? '' : 'disabled' }}>
                        Submit Registration
                    </button>
                </form>

                <p class="form-note">By submitting, the participant agrees to the community event privacy terms.</p>
            </div>

            <div class="card attendees-panel">
                <div class="card-head attendees-head">
                    <div>
                        <h3>Registered Attendees</h3>
                        <p>View and manage current list of participants.</p>
                    </div>

                    <div class="attendee-tools">
                        <form method="GET" class="attendee-search-form">
                            <input type="hidden" name="event_id" value="{{ $selectedEventId }}">
                            <div class="search-field">
                                <span class="search-icon" aria-hidden="true">⌕</span>
                                <input type="text" name="q" value="{{ $search }}" placeholder="Search names, email..." data-registration-search oninput="this.form.submit()">
                            </div>
                        </form>

                        <a class="icon-action export-action" href="{{ route('registrations.index', array_filter(['event_id' => $selectedEventId, 'q' => $search, 'export' => 'xlsx'])) }}" title="Export XLSX" aria-label="Export XLSX">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4v10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 20h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </a>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table registrations-table">
                        <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($registrations as $registration)
                            <tr>
                                <td data-label="Participant">
                                    <div class="attendee-cell">
                                        <div class="avatar">
                                            {{ strtoupper(substr($registration->first_name, 0, 1) . substr($registration->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $registration->full_name }}</strong>
                                            <div class="muted">Reg: {{ $registration->created_at->format('Y-m-d') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email">{{ $registration->email }}</td>
                                <td data-label="Contact">{{ $registration->contact_number }}</td>
                                <td data-label="Status"><span class="badge {{ strtolower($registration->attendance_status) }}">{{ $registration->attendance_status }}</span></td>
                                <td data-label="Action">
                                    <div class="actions">
                                        <a href="{{ route('registrations.edit', $registration) }}" class="icon-btn" title="View" aria-label="View registration">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </a>
                                        <a href="{{ route('registrations.edit', $registration) }}" class="icon-btn" title="Edit" aria-label="Edit registration">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 21v-3.75L14.81 5.44a2 2 0 012.83 0l1.93 1.93a2 2 0 010 2.83L7.75 21H3z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('registrations.destroy', $registration) }}" onsubmit="return confirm('Remove this registrant?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn danger" title="Delete" aria-label="Delete registration">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 6v12a2 2 0 002 2h4a2 2 0 002-2V6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        No registrations found for this event.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="registrations-footer">
                    <div class="pagination-summary">
                        Showing <strong>{{ $registrations->firstItem() ?? 0 }}</strong>
                        to <strong>{{ $registrations->lastItem() ?? 0 }}</strong>
                        of <strong>{{ $registrations->total() }}</strong> participants
                    </div>

                    <nav class="compact-pagination" aria-label="Registrations pagination">
                        <a class="page-pill {{ $registrations->onFirstPage() ? 'is-disabled' : '' }}"
                           href="{{ $registrations->previousPageUrl() ?? '#' }}"
                           aria-disabled="{{ $registrations->onFirstPage() ? 'true' : 'false' }}"
                           tabindex="{{ $registrations->onFirstPage() ? '-1' : '0' }}">Previous</a>
                        <a class="page-pill {{ $registrations->hasMorePages() ? '' : 'is-disabled' }}"
                           href="{{ $registrations->nextPageUrl() ?? '#' }}"
                           aria-disabled="{{ $registrations->hasMorePages() ? 'false' : 'true' }}"
                           tabindex="{{ $registrations->hasMorePages() ? '0' : '-1' }}">Next</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

@endsection
