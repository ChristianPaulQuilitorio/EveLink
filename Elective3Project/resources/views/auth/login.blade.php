@extends('layouts.auth')

@section('content')
    <div class="auth-grid">
        <section class="auth-hero">
            <span class="auth-badge">Admin Portal</span>
            <div>
                <h1>EveLink</h1>
                <p>Manage registrations, track attendance, and export reports from one calm workspace.</p>
            </div>

            <ul class="auth-feature-list">
                <li>Unified event operations for admin workflows</li>
                <li>Fast attendance and registration management</li>
                <li>XLSX-ready reporting with clean exports</li>
            </ul>
        </section>

        <section class="auth-panel">
            <div class="auth-panel-head">
                <h2>Welcome back</h2>
                <p>Sign in to your administrative dashboard.</p>
            </div>

            @if ($errors->has('login'))
                <div class="alert error">{{ $errors->first('login') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form form-grid">
                @csrf

                <label for="login">Email or Username
                    <input id="login" type="text" name="login" value="{{ old('login') }}" autocomplete="username" placeholder="admin@example.com" required>
                </label>

                <label for="password">Password
                    <input id="password" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                </label>

                <button class="btn btn-primary" type="submit">Log in</button>
            </form>

            <div class="auth-portal-link">
                <span>Looking for the attendee experience?</span>
                <a href="{{ route('portal.home') }}">Open the EveLink user / attendee portal</a>
            </div>

            <p class="auth-footnote">EveLink Admin Portal</p>
        </section>
    </div>
@endsection
