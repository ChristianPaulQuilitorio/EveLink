@extends('layouts.auth')

@section('content')
    <div class="auth-grid">
        <section class="auth-hero">
            <span class="auth-badge">Attendee Sign Up</span>
            <div>
                <h1>Join EveLink</h1>
                <p>Create an attendee account to register for events and keep your bookings organized.</p>
            </div>

            <ul class="auth-feature-list">
                <li>One account for all event registrations</li>
                <li>Fast event joining after sign-up</li>
                <li>Personal registration history</li>
            </ul>
        </section>

        <section class="auth-panel">
            <div class="auth-panel-head">
                <h2>Create Account</h2>
                <p>Fill in your attendee details below.</p>
            </div>

            @if ($errors->any())
                <div class="alert error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('portal.register.store') }}" class="auth-form form-grid">
                @csrf

                <label>Username
                    <input 
                        type="text" 
                        name="username" 
                        value="{{ old('username') }}" 
                        autocomplete="username" 
                        placeholder="attendee123"
                        pattern="[a-zA-Z0-9_]{3,50}"
                        title="Username: 3-50 characters (letters, numbers, underscores only)"
                        required>
                    @error('username')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <label>Full Name
                    <input 
                        type="text" 
                        name="full_name" 
                        value="{{ old('full_name') }}" 
                        autocomplete="name" 
                        placeholder="Juan Dela Cruz"
                        pattern="[a-zA-Z\s]{2,100}"
                        title="Full name: 2-100 characters (letters and spaces only)"
                        required>
                    @error('full_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <label>Email Address
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        autocomplete="email" 
                        placeholder="juan@example.com"
                        required>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <label>Contact Number
                    <input 
                        type="text" 
                        name="contact_number" 
                        value="{{ old('contact_number') }}" 
                        placeholder="09123456789"
                        pattern="09[0-9]{9}"
                        title="Contact number: 11 digits starting with 09"
                        maxlength="11"
                        inputmode="numeric"
                        required>
                    @error('contact_number')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <label>Password
                    <input 
                        type="password" 
                        name="password" 
                        autocomplete="new-password" 
                        placeholder="Create a strong password"
                        title="Password: 8+ characters with uppercase, lowercase, and numbers"
                        required>
                    <small>Min 8 characters, uppercase, lowercase, and number required</small>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <label>Confirm Password
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        autocomplete="new-password" 
                        placeholder="Re-enter password"
                        required>
                    @error('password_confirmation')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <button class="btn btn-primary" type="submit">Create Account</button>
            </form>

            <p class="auth-footnote">
                Already have an account?
                <a href="{{ route('portal.login') }}">Log in here</a>
            </p>
        </section>
    </div>
@endsection
