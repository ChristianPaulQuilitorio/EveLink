@extends('layouts.app')

@section('content')
    @php
        $eventStatusLabel = $selectedEvent
            ? ($selectedEvent->status === 'Open' ? 'Session In Progress' : $selectedEvent->status)
            : 'No Active Event';
        $eventDateLabel = $selectedEvent ? $selectedEvent->event_date->format('M d, Y') : 'No date';
        $eventVenueLabel = $selectedEvent?->venue ?? 'No venue selected';
    @endphp

    <div class="page-header with-actions attendance-header">
        <div>
            <h1>Attendance Tracking</h1>
            <p>Manage real-time attendance for registered participants.</p>
        </div>
    </div>

    @if($selectedEvent)
        <div class="attendance-shell">
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

            <div class="card attendance-status-card">
                <div class="attendance-meta">
                    <div class="attendance-stat">
                        <div class="attendance-icon">📊</div>
                        <div>
                            <span>Event Status</span>
                            <strong>{{ $eventStatusLabel }}</strong>
                        </div>
                    </div>
                    <div class="attendance-summary">
                        <span>Date & Venue</span>
                        <strong>{{ $eventDateLabel }}</strong>
                        <span style="font-size: 11px; color: var(--muted);">{{ $eventVenueLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="card attendance-table-card">
                <div class="attendance-table-head">
                    <div>
                        <h3>Registered Attendees</h3>
                        <p>View and manage current list of participants.</p>
                    </div>
                </div>

                <div class="attendee-tools">
                    <form method="GET" class="attendance-search-form">
                        <input type="hidden" name="event_id" value="{{ $selectedEventId }}">
                        <label class="search-field">
                            <span class="search-icon">⌕</span>
                            <input type="text" name="q" value="{{ $search }}" placeholder="Search attendee by name..." oninput="this.form.submit()">
                        </label>
                    </form>

                    <form method="GET" class="attendance-filter-form">
                        <input type="hidden" name="event_id" value="{{ $selectedEventId }}">
                        <input type="hidden" name="q" value="{{ $search }}">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">Filter by Status</option>
                            @foreach(['Present', 'Absent', 'Pending'] as $option)
                                <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="table-wrap">
                    <table class="table attendance-table">
                        <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" disabled aria-label="Select all attendees">
                            </th>
                            <th>Participant Name</th>
                            <th>Contact Details</th>
                            <th>Time of Present</th>
                            <th>Attendance Status</th>
                            <th style="width:52px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($registrations as $registration)
                            <tr>
                                <td>
                                    <input type="checkbox" disabled aria-label="Select attendee">
                                </td>
                                <td>
                                    <div class="attendance-name-cell">
                                        <strong>{{ $registration->full_name }}</strong>
                                        <span>{{ $registration->email }}</span>
                                    </div>
                                </td>
                                <td>{{ $registration->contact_number }}</td>
                                <td>
                                    {{ $registration->attendance_status === 'Present'
                                        ? optional($registration->present_at ?? $registration->updated_at)->format('h:i a')
                                        : '-- / -- / --' }}
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('attendance.update', $registration) }}" class="attendance-form">
                                        @csrf
                                        @method('PATCH')
                                        <div class="attendance-chip-group">
                                            @foreach(['Present', 'Absent', 'Pending'] as $option)
                                                <button type="submit" name="attendance_status" value="{{ $option }}" class="attendance-chip {{ $registration->attendance_status === $option ? 'is-active' : '' }}">
                                                    {{ $option }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <button type="button" class="attendance-more" aria-label="More actions">⋮</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="attendance-empty">No participants for this event.</div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="attendees-footer">
                <div class="attendance-footer-summary">
                    <div>
                        <span>Total Attendees</span>
                        <strong>{{ $summary['total'] }}</strong>
                    </div>
                    <span class="attendance-footer-divider"></span>
                    <div>
                        <span class="summary-badge present">{{ $summary['Present'] }}</span>
                        <span>Present</span>
                    </div>
                    <div>
                        <span class="summary-badge absent">{{ $summary['Absent'] }}</span>
                        <span>Absent</span>
                    </div>
                    <div>
                        <span class="summary-badge pending">{{ $summary['Pending'] }}</span>
                        <span>Pending</span>
                    </div>
                </div>

                <div class="registrations-footer">
                    <a class="btn" href="{{ route('attendance.index', ['event_id' => $selectedEventId]) }}">Reset List</a>
                    <a class="btn btn-primary" href="{{ route('attendance.index', array_filter(['event_id' => $selectedEventId, 'q' => $search, 'status' => $status, 'export' => 'xlsx'])) }}">Export Attendance (XLSX)</a>
                </div>
            </div>

        @php
            $currentPage = $registrations->currentPage();
            $lastPage = $registrations->lastPage();
            $paginationItems = [];

            if ($lastPage <= 7) {
                $paginationItems = range(1, $lastPage);
            } else {
                $paginationItems[] = 1;

                $start = max(2, $currentPage - 1);
                $end = min($lastPage - 1, $currentPage + 1);

                if ($start > 2) {
                    $paginationItems[] = '...';
                }

                for ($page = $start; $page <= $end; $page++) {
                    $paginationItems[] = $page;
                }

                if ($end < $lastPage - 1) {
                    $paginationItems[] = '...';
                }

                $paginationItems[] = $lastPage;
            }
        @endphp

        <div class="pagination-footer attendance-pagination">
            <div class="pagination-summary">
                SHOWING <strong>{{ $registrations->firstItem() ?? 0 }}</strong>
                TO <strong>{{ $registrations->lastItem() ?? 0 }}</strong>
                OF <strong>{{ $registrations->total() }}</strong> PARTICIPANTS
            </div>

            <nav class="event-pagination" aria-label="Attendance pagination">
                <a class="page-pill {{ $registrations->onFirstPage() ? 'is-disabled' : '' }}"
                   href="{{ $registrations->previousPageUrl() ?? '#' }}"
                   aria-disabled="{{ $registrations->onFirstPage() ? 'true' : 'false' }}"
                   tabindex="{{ $registrations->onFirstPage() ? '-1' : '0' }}">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M12.5 4.5L7.5 10l5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Prev</span>
                </a>

                @foreach ($paginationItems as $item)
                    @if ($item === '...')
                        <span class="page-ellipsis" aria-hidden="true">...</span>
                    @else
                        <a class="page-number {{ (int) $item === $currentPage ? 'is-active' : '' }}"
                           href="{{ $registrations->url($item) }}"
                           aria-current="{{ (int) $item === $currentPage ? 'page' : 'false' }}">
                            {{ $item }}
                        </a>
                    @endif
                @endforeach

                <a class="page-pill {{ $registrations->hasMorePages() ? '' : 'is-disabled' }}"
                   href="{{ $registrations->nextPageUrl() ?? '#' }}"
                   aria-disabled="{{ $registrations->hasMorePages() ? 'false' : 'true' }}"
                   tabindex="{{ $registrations->hasMorePages() ? '0' : '-1' }}">
                    <span>Next</span>
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7.5 4.5L12.5 10l-5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </nav>
        </div>
        </div>
    @else
        <div class="card">No event selected.</div>
    @endif
@endsection
