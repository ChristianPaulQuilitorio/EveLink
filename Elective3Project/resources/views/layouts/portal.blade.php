<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2583f6">
    <title>{{ $title ?? 'EveLink Portal' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body">
<header class="portal-topbar">
    <a class="portal-brand" href="{{ route('portal.home') }}">
        <span class="portal-brand-mark">EL</span>
        <span>
            <strong>EveLink</strong>
            <small>Community events for everyone</small>
        </span>
    </a>

    <nav class="portal-nav">
        <a href="{{ route('portal.home') }}" class="{{ request()->routeIs('portal.home') ? 'is-active' : '' }}">Events</a>
        @auth
            @if(auth()->user()->role === 'attendee')
                <a href="{{ route('portal.registrations') }}" class="{{ request()->routeIs('portal.registrations') ? 'is-active' : '' }}">My Registrations</a>
            @endif
        @endauth
    </nav>

    <div class="portal-actions">
        @guest
            <a class="btn" href="{{ route('portal.login') }}">Log In</a>
            <a class="btn btn-primary" href="{{ route('portal.register') }}">Create Account</a>
        @else
            <div class="portal-user-chip">
                <strong>{{ auth()->user()->display_name }}</strong>
                <span>{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Attendee' }}</span>
            </div>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="btn">Log Out</button>
            </form>
        @endguest
    </div>
</header>

<main class="portal-main">
    @if (session('success'))
        <div class="alert success portal-alert">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error portal-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="portal-footer">
    <span>© {{ now()->year }} EveLink. All rights reserved.</span>
</footer>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js');
    });
}
</script>
</body>
</html>
