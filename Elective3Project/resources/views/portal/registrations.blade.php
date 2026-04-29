@extends('layouts.portal')

@section('content')
    <section class="portal-section">
        <div class="section-head">
            <div>
                <h1>My Registrations</h1>
                <p>Track every event you have joined.</p>
            </div>
        </div>

        <div class="card portal-table-card">
            <table class="table portal-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                        <tr>
                            <td>
                                <strong>{{ $registration->event?->event_name }}</strong>
                                <div class="muted">Joined {{ $registration->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div>{{ $registration->event?->event_date?->format('F d, Y') }}</div>
                                <div class="muted">{{ $registration->event?->time_range ?? 'TBD' }}</div>
                            </td>
                            <td>{{ $registration->event?->venue }}</td>
                            <td><span class="badge {{ strtolower($registration->attendance_status) }}">{{ $registration->attendance_status }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn" href="{{ route('portal.events.show', $registration->event) }}">View Event</a>
                                    @php
                                        $eventPassed = $registration->event?->event_date && (string)$registration->event->event_date < now()->toDateString();
                                    @endphp
                                    @if(!$eventPassed)
                                        <form method="POST" action="{{ route('portal.events.quit', $registration->event) }}" style="display: inline;" onsubmit="return confirm('Leave this event?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">Leave</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="portal-empty">You have not joined any events yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-footer portal-pagination">
            <div class="pagination-summary">
                Showing <strong>{{ $registrations->firstItem() ?? 0 }}</strong> to <strong>{{ $registrations->lastItem() ?? 0 }}</strong> of <strong>{{ $registrations->total() }}</strong> registrations
            </div>
            <div class="compact-pagination">
                <a class="page-pill {{ $registrations->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $registrations->previousPageUrl() ?? '#' }}">Previous</a>
                <a class="page-pill {{ $registrations->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $registrations->nextPageUrl() ?? '#' }}">Next</a>
            </div>
        </div>
    </section>
@endsection
