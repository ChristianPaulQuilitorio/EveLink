@extends('layouts.app')

@section('content')
    @php
        $calendarStart = now()->startOfMonth();
        $calendarEnd = now()->endOfMonth();
        $showDashboardRegisterModal = $errors->any() && old('dashboard_register') === '1';
        $padStart = $calendarStart->copy()->dayOfWeek;
        $days = [];

        for ($i = 0; $i < $padStart; $i++) {
            $days[] = null;
        }

        for ($d = 1; $d <= $calendarEnd->day; $d++) {
            $days[] = $d;
        }

        while (count($days) % 7 !== 0) {
            $days[] = null;
        }
    @endphp

    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Manage your barangay events and registrations at a glance.</p>
        </div>
    </div>

    <div class="dashboard-linear">
        <div class="card welcome-banner">
            <div>
                <h3>Welcome back,<br>{{ auth()->user()->display_name }}!</h3>
                <p>You have <a href="{{ route('events.index') }}">{{ $upcomingEvents }} upcoming events</a> this week.<br>Ready to manage your attendees?</p>
            </div>
            <div class="row">
                <a class="btn btn-primary" href="{{ route('events.index', ['create' => 1]) }}">+ Add New Event</a>
                <button type="button" class="btn" data-open-dashboard-register-modal>Register Attendee</button>
            </div>
        </div>

        <div class="cards four dashboard-stats">
            <div class="card stat plus-hint">
                <div class="stat-top"><span class="stat-icon">&#128197;</span></div>
                <span>Total Events</span>
                <strong>{{ $totalEvents }}</strong>
            </div>
            <div class="card stat plus-hint">
                <div class="stat-top"><span class="stat-icon">&#128101;</span></div>
                <span>Registrations</span>
                <strong>{{ $totalRegistrations }}</strong>
            </div>
            <div class="card stat plus-hint">
                <div class="stat-top"><span class="stat-icon">&#127942;</span><em>{{ $fullEvents > 0 ? $fullEvents . ' ending soon' : 'No full events' }}</em></div>
                <span>Full Events</span>
                <strong>{{ $fullEvents }}</strong>
            </div>
            <div class="card stat plus-hint">
                <div class="stat-top"><span class="stat-icon">&#128339;</span><em>{{ $upcomingEvents > 0 ? 'Next: Tomorrow' : 'No upcoming' }}</em></div>
                <span>Upcoming</span>
                <strong>{{ $upcomingEvents }}</strong>
            </div>
        </div>

        <div class="card calendar-card">
            <h3>{{ now()->format('F Y') }}</h3>
            <div class="calendar-head">
                @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $weekday)
                    <span>{{ $weekday }}</span>
                @endforeach
            </div>
            <div class="calendar-grid">
                @foreach($days as $day)
                    <span class="{{ $day === now()->day ? 'today' : '' }}">{{ $day ?? '' }}</span>
                @endforeach
            </div>
        </div>

        <div class="card recent-registrations-card">
            <div class="card-head">
                <div>
                    <h3>Recent Registrations</h3>
                    <p>Latest attendee sign-ups across all events.</p>
                </div>
                <a href="{{ route('registrations.index') }}" class="view-all">View All</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Attendee</th>
                        <th>Event Name</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentRegistrations as $registration)
                        <tr>
                            <td>
                                <div class="attendee-cell">
                                    <span class="avatar">{{ strtoupper(substr($registration->first_name, 0, 1) . substr($registration->last_name, 0, 1)) }}</span>
                                    <span>{{ $registration->full_name }}</span>
                                </div>
                            </td>
                            <td>{{ $registration->event?->event_name }}</td>
                            <td>{{ $registration->created_at->diffForHumans() }}</td>
                            <td><span class="badge {{ strtolower($registration->attendance_status) }}">{{ $registration->attendance_status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No registrations yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card upcoming-events-card">
            <h3>Upcoming Events</h3>
            <ul class="upcoming-list">
                @forelse($upcomingList as $event)
                    <li>
                        <span class="date-chip">
                            <small>{{ strtoupper($event->event_date->format('M')) }}</small>
                            <strong>{{ $event->event_date->format('d') }}</strong>
                        </span>
                        <div>
                            <strong>{{ $event->event_name }}</strong>
                            <p>{{ $event->start_time ?: '09:00 AM' }} · {{ $event->remaining_slots }}/{{ $event->max_slots }}</p>
                        </div>
                    </li>
                @empty
                    <li class="empty">No upcoming events.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="modal-overlay dashboard-register-modal {{ $showDashboardRegisterModal ? 'is-open' : '' }}" id="dashboardRegisterModal">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="dashboardRegisterTitle">
            <div class="modal-head">
                <div>
                    <h2 id="dashboardRegisterTitle">Register Attendee</h2>
                    <p>Quickly add an attendee from the dashboard with live event search.</p>
                </div>
                <button type="button" class="modal-close" aria-label="Close" data-close-dashboard-register-modal>&times;</button>
            </div>

            @if ($showDashboardRegisterModal)
                <div class="modal-alert">
                    <strong>Validation Error</strong>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('registrations.store') }}" class="form-grid two-col modal-form dashboard-register-form">
                @csrf
                <input type="hidden" name="dashboard_register" value="1">

                <label class="span-2">Search Event
                    <input
                        type="text"
                        placeholder="Type event title or venue..."
                        data-dashboard-event-search
                        autocomplete="off"
                    >
                </label>

                <label class="span-2">Event
                    <select name="event_id" data-dashboard-event-select required>
                        <option value="">Select an event</option>
                        @foreach($registrationEvents as $event)
                            @php
                                $label = $event->event_name . ' · ' . $event->event_date->format('M d, Y') . ' · ' . $event->venue;
                            @endphp
                            <option
                                value="{{ $event->id }}"
                                data-search="{{ strtolower($event->event_name . ' ' . $event->venue . ' ' . $event->event_date->format('Y-m-d')) }}"
                                {{ (string) old('event_id') === (string) $event->id ? 'selected' : '' }}
                            >{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>First Name
                    <input type="text" name="first_name" value="{{ old('first_name') }}" maxlength="50" required>
                </label>

                <label>Last Name
                    <input type="text" name="last_name" value="{{ old('last_name') }}" maxlength="50" required>
                </label>

                <label>Email Address
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="100" required>
                </label>

                <label>Contact Number
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" pattern="[0-9]{11}" maxlength="11" placeholder="09XXXXXXXXX" required>
                </label>

                <div class="row span-2 modal-actions">
                    <button type="button" class="btn" data-close-dashboard-register-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Registration</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('dashboardRegisterModal');
            const openButton = document.querySelector('[data-open-dashboard-register-modal]');
            const closeButtons = document.querySelectorAll('[data-close-dashboard-register-modal]');
            const eventSearch = document.querySelector('[data-dashboard-event-search]');
            const eventSelect = document.querySelector('[data-dashboard-event-select]');

            const openModal = function () {
                if (!modal) {
                    return;
                }
                modal.classList.add('is-open');
                document.body.classList.add('modal-open');
            };

            const closeModal = function () {
                if (!modal) {
                    return;
                }
                modal.classList.remove('is-open');
                document.body.classList.remove('modal-open');
            };

            if (modal && modal.classList.contains('is-open')) {
                document.body.classList.add('modal-open');
            }

            openButton?.addEventListener('click', openModal);

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            modal?.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                    closeModal();
                }
            });

            if (eventSearch && eventSelect) {
                eventSearch.addEventListener('input', function () {
                    const term = eventSearch.value.trim().toLowerCase();
                    const options = Array.from(eventSelect.options).slice(1);
                    let firstVisible = null;

                    options.forEach(function (option) {
                        const haystack = option.dataset.search || option.textContent.toLowerCase();
                        const visible = term === '' || haystack.includes(term);
                        option.hidden = !visible;

                        if (visible && !firstVisible) {
                            firstVisible = option;
                        }
                    });

                    if (eventSelect.selectedOptions.length > 0 && eventSelect.selectedOptions[0]?.hidden) {
                        eventSelect.value = '';
                    }

                    if (!eventSelect.value && firstVisible) {
                        eventSelect.value = firstVisible.value;
                    }
                });
            }
        })();
    </script>
@endsection
