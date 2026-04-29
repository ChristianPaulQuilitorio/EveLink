@extends('layouts.auth')

@section('content')
    <div class="auth-grid">
        <section class="auth-hero">
            <span class="auth-badge">Attendee Portal</span>
            <div>
                <h1>EveLink</h1>
                <p>Sign in to browse events, reserve a seat, and manage your registrations from one account.</p>
            </div>

            <ul class="auth-feature-list">
                <li>Browse live community events</li>
                <li>Join events instantly after sign-in</li>
                <li>Track your registrations in one place</li>
            </ul>
        </section>

        <section class="auth-panel">
            <div class="auth-panel-head">
                <h2>Welcome back</h2>
                <p>Log in to your attendee account.</p>
            </div>

            @if ($errors->has('login'))
                <div class="alert error">{{ $errors->first('login') }}</div>
            @endif

            <form method="POST" action="{{ route('portal.login.store') }}" class="auth-form form-grid">
                @csrf

                <label for="login">Email or Username
                    <input 
                        id="login" 
                        type="text" 
                        name="login" 
                        value="{{ old('login') }}" 
                        autocomplete="username" 
                        placeholder="attendee@example.com or attendee123"
                        maxlength="100"
                        required>
                    @error('login')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <label for="password">Password
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        autocomplete="current-password" 
                        placeholder="Enter your password"
                        minlength="8"
                        required>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </label>

                <button class="btn btn-primary" type="submit">Log in</button>
            </form>

            <p class="auth-footnote">
                New here?
                <a href="{{ route('portal.register') }}">Create an attendee account</a>
            </p>
        </section>
    </div>
@endsection
