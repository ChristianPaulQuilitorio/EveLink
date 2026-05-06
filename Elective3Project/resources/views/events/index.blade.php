@extends('layouts.app')

@section('content')
    @php
        $editingEventId = old('editing_event_id');
        $showCreateModal = (bool) request()->boolean('create') || ($errors->any() && blank($editingEventId));
    @endphp

    <div class="page-header with-actions">
        <div>
            <h1>Community Events</h1>
            <p>Manage, schedule, and monitor local events.</p>
        </div>
        <button type="button" class="btn btn-primary" data-open-create-event-modal>Create New Event</button>
    </div>

    <div class="cards four">
        <div class="card stat"><span>Total Scheduled</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="card stat"><span>Open Registration</span><strong>{{ $stats['open'] }}</strong></div>
        <div class="card stat"><span>Fully Booked</span><strong>{{ $stats['full'] }}</strong></div>
        <div class="card stat"><span>Past Events</span><strong>{{ $stats['past'] }}</strong></div>
    </div>

    <div class="card">
        <form method="GET" class="filters auto-submit-filters" data-auto-submit-filters>
            <label class="search-field">
                <span class="search-icon">⌕</span>
                <input type="text" name="q" value="{{ $search }}" placeholder="Search by event title or venue" oninput="this.form.submit()">
            </label>
            <select name="status" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['Open', 'Full', 'Concluded'] as $opt)
                    <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </form>

        <table class="table">
            <thead>
            <tr>
                <th>Event Detail</th>
                <th>Schedule</th>
                <th>Venue</th>
                <th>Capacity</th>
                <th>Status</th>
                <th style="width:160px;">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($events as $event)
                <tr>
                    <td data-label="Event Detail">
                        <strong>{{ $event->event_name }}</strong>
                        <div class="muted">ID: EVT-{{ str_pad((string)$event->id, 4, '0', STR_PAD_LEFT) }}</div>
                    </td>
                    <td data-label="Schedule">
                        {{ $event->event_date->format('M d, Y') }}
                        <div class="muted">{{ $event->start_time ?: '--:--' }} - {{ $event->end_time ?: '--:--' }}</div>
                    </td>
                    <td data-label="Venue">{{ $event->venue }}</td>
                    <td data-label="Capacity">{{ $event->registered_count }}/{{ $event->max_slots }} <span class="muted">({{ $event->remaining_slots }} left)</span></td>
                    <td data-label="Status"><span class="badge {{ strtolower($event->status) }}">{{ $event->status }}</span></td>
                    <td data-label="Action" class="actions">
                        <a href="{{ route('events.show', $event) }}" class="icon-btn" title="View" aria-label="View">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7z" stroke="#59677b" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="#59677b" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                        <button type="button" class="icon-btn" title="Edit" aria-label="Edit" data-open-edit-event-modal="{{ $event->id }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 21v-3.75L14.81 5.44a2 2 0 012.83 0l1.93 1.93a2 2 0 010 2.83L7.75 21H3z" stroke="#59677b" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event? This also deletes registrations.');" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn danger" title="Delete" aria-label="Delete">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18" stroke="#c04545" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 6v12a2 2 0 002 2h4a2 2 0 002-2V6" stroke="#c04545" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="#c04545" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No events found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @php
            $currentPage = $events->currentPage();
            $lastPage = $events->lastPage();
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

        <div class="pagination-footer">
            <div class="pagination-summary">
                SHOWING <strong>{{ $events->firstItem() ?? 0 }}</strong>
                TO <strong>{{ $events->lastItem() ?? 0 }}</strong>
                OF <strong>{{ $events->total() }}</strong> ENTRIES
            </div>

            <nav class="event-pagination" aria-label="Events pagination">
                <a class="page-pill {{ $events->onFirstPage() ? 'is-disabled' : '' }}"
                   href="{{ $events->previousPageUrl() ?? '#' }}"
                   aria-disabled="{{ $events->onFirstPage() ? 'true' : 'false' }}"
                   tabindex="{{ $events->onFirstPage() ? '-1' : '0' }}">
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M12.5 4.5L7.5 10l5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Prev</span>
                </a>

                @foreach ($paginationItems as $item)
                    @if ($item === '...')
                        <span class="page-ellipsis" aria-hidden="true">...</span>
                    @else
                        <a class="page-number {{ (int) $item === $currentPage ? 'is-active' : '' }}"
                           href="{{ $events->url($item) }}"
                           aria-current="{{ (int) $item === $currentPage ? 'page' : 'false' }}">
                            {{ $item }}
                        </a>
                    @endif
                @endforeach

                <a class="page-pill {{ $events->hasMorePages() ? '' : 'is-disabled' }}"
                   href="{{ $events->nextPageUrl() ?? '#' }}"
                   aria-disabled="{{ $events->hasMorePages() ? 'false' : 'true' }}"
                   tabindex="{{ $events->hasMorePages() ? '0' : '-1' }}">
                    <span>Next</span>
                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7.5 4.5L12.5 10l-5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </nav>
        </div>
    </div>

    <div class="modal-overlay {{ $showCreateModal ? 'is-open' : '' }}" id="createEventModal">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="createEventTitle">
            <div class="modal-head">
                <div>
                    <h2 id="createEventTitle">Add New Event</h2>
                    <p>Fill in the details below to schedule a new community event.</p>
                </div>
                <button type="button" class="modal-close" aria-label="Close" data-close-create-event-modal>&times;</button>
            </div>

            @if ($errors->any())
                <div class="modal-alert">
                    <strong>Validation Error</strong>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('events.store') }}" class="form-grid two-col modal-form">
                @csrf

                <label class="span-2">Event Name
                    <input type="text" name="event_name" value="{{ old('event_name') }}" placeholder="e.g. Community Clean-up Drive" required>
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
                    <input type="number" name="max_slots" min="1" value="{{ old('max_slots') }}" required>
                </label>

                <label>Venue
                    <input type="text" name="venue" value="{{ old('venue') }}" placeholder="Enter location" required>
                </label>

                <label class="span-2">About the Event
                    <textarea name="description" rows="4" placeholder="Describe the event purpose, requirements, and what attendees should bring.">{{ old('description') }}</textarea>
                </label>

                <label class="span-2">Event Highlight
                    <textarea name="event_highlight" rows="3" placeholder="Describe the event purpose, requirements, and what attendees should bring.">{{ old('event_highlight') }}</textarea>
                </label>

                <div class="row span-2 modal-actions">
                    <button type="button" class="btn" data-close-create-event-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($events as $event)
        @php
            $showEditModal = (string) $editingEventId === (string) $event->id;
        @endphp
        <div class="modal-overlay {{ $showEditModal ? 'is-open' : '' }}" id="editEventModal-{{ $event->id }}">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editEventTitle-{{ $event->id }}">
                <div class="modal-head">
                    <div>
                        <h2 id="editEventTitle-{{ $event->id }}">Edit Event</h2>
                        <p>Update the event details below.</p>
                    </div>
                    <button type="button" class="modal-close" aria-label="Close" data-close-edit-event-modal>&times;</button>
                </div>

                <form method="POST" action="{{ route('events.update', $event) }}" class="form-grid two-col modal-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="editing_event_id" value="{{ $event->id }}">

                    <label class="span-2">Event Name
                        <input type="text" name="event_name" value="{{ old('editing_event_id', $editingEventId) == $event->id ? old('event_name', $event->event_name) : $event->event_name }}" required>
                    </label>

                    <label>Date
                        <input type="date" name="event_date" value="{{ old('editing_event_id', $editingEventId) == $event->id ? old('event_date', $event->event_date->format('Y-m-d')) : $event->event_date->format('Y-m-d') }}" required>
                    </label>

                    <label>Start Time
                        <input type="time" name="start_time" value="{{ old('editing_event_id', $editingEventId) == $event->id ? old('start_time', $event->start_time) : $event->start_time }}">
                    </label>

                    <label>End Time
                        <input type="time" name="end_time" value="{{ old('editing_event_id', $editingEventId) == $event->id ? old('end_time', $event->end_time) : $event->end_time }}">
                    </label>

                    <label>Max Slots
                        <input type="number" name="max_slots" min="{{ max(1, $event->registrations_count) }}" value="{{ old('editing_event_id', $editingEventId) == $event->id ? old('max_slots', $event->max_slots) : $event->max_slots }}" required>
                    </label>

                    <label>Venue
                        <input type="text" name="venue" value="{{ old('editing_event_id', $editingEventId) == $event->id ? old('venue', $event->venue) : $event->venue }}" required>
                    </label>

                    <label class="span-2">About the Event
                        <textarea name="description" rows="4">{{ old('editing_event_id', $editingEventId) == $event->id ? old('description', $event->description) : $event->description }}</textarea>
                    </label>

                    <div class="row span-2 modal-actions">
                        <button type="button" class="btn" data-close-edit-event-modal>Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        (function () {
            const createModal = document.getElementById('createEventModal');
            const editButtons = document.querySelectorAll('[data-open-edit-event-modal]');
            const openButtons = document.querySelectorAll('[data-open-create-event-modal]');
            const closeCreateButtons = document.querySelectorAll('[data-close-create-event-modal]');
            const closeEditButtons = document.querySelectorAll('[data-close-edit-event-modal]');
            const autoFilters = document.querySelector('[data-auto-submit-filters]');
            let autoSubmitTimer = null;

            const openModal = function (modal) {
                if (!modal) {
                    return;
                }

                modal.classList.add('is-open');
                document.body.classList.add('modal-open');
            };

            const closeModal = function (modal) {
                if (!modal) {
                    return;
                }

                modal.classList.remove('is-open');

                if (!document.querySelector('.modal-overlay.is-open')) {
                    document.body.classList.remove('modal-open');
                }
            };

            if (autoFilters) {
                const submitFilters = function () {
                    window.clearTimeout(autoSubmitTimer);
                    autoSubmitTimer = window.setTimeout(function () {
                        autoFilters.submit();
                    }, 220);
                };

                autoFilters.querySelectorAll('input').forEach(function (input) {
                    input.addEventListener('input', submitFilters);
                });

                autoFilters.querySelectorAll('select').forEach(function (select) {
                    select.addEventListener('change', function () {
                        autoFilters.submit();
                    });
                });
            }

            openButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    openModal(createModal);
                });
            });

            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const modal = document.getElementById('editEventModal-' + button.dataset.openEditEventModal);
                    openModal(modal);
                });
            });

            closeCreateButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    closeModal(createModal);
                });
            });

            closeEditButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const modal = button.closest('.modal-overlay');
                    closeModal(modal);
                });
            });

            document.querySelectorAll('.modal-overlay').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            if (document.querySelector('.modal-overlay.is-open')) {
                document.body.classList.add('modal-open');
            }
        })();
    </script>
@endsection

