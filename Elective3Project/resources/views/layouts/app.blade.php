<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2583f6">
    <title>{{ $title ?? 'EveLink Admin' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="app-body">
<div class="app-shell">
    <aside class="sidebar" id="appSidebar">
        <div class="brand">
            <span class="brand-mark">EL</span>
            <div class="brand-copy">
                <strong>EveLink</strong>
                <strong>Event Registration &amp; Attendee Management</strong>
            </div>
        </div>
        <nav class="menu">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="menu-icon" title="Dashboard"><img src="/icons/dashboard.svg" alt="Dashboard" style="width:18px;height:18px;"></span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">
                <span class="menu-icon" title="Events"><img src="/icons/event.svg" alt="Events" style="width:18px;height:18px;"></span>
                <span>Events</span>
            </a>
            <a href="{{ route('registrations.index') }}" class="{{ request()->routeIs('registrations.*') ? 'active' : '' }}">
                <span class="menu-icon" title="Registrations"><img src="/icons/registrations.svg" alt="Registrations" style="width:18px;height:18px;"></span>
                <span>Registrations</span>
            </a>
            <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <span class="menu-icon" title="Attendance"><img src="/icons/attendance.svg" alt="Attendance" style="width:18px;height:18px;"></span>
                <span>Attendance</span>
            </a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="link-button">Sign Out</button>
        </form>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open menu">&#9776;</button>
                <div class="topbar-title">ADMIN COMMAND CENTER</div>
            </div>
            <div class="topbar-right">
                <span class="topbar-bell" aria-hidden="true">&#128276;</span>
                <div class="topbar-user">
                    <strong>{{ auth()->user()->display_name }}</strong>
                    <span>Event Administrator</span>
                </div>
            </div>
        </header>

        <section class="content">
            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>
</div>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js');
    });
}

(function () {
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const menuLinks = document.querySelectorAll('.menu a');

    if (!toggle || !backdrop) {
        return;
    }

    const openSidebar = function () {
        document.body.classList.add('sidebar-open');
    };

    const closeSidebar = function () {
        document.body.classList.remove('sidebar-open');
    };

    toggle.addEventListener('click', openSidebar);
    backdrop.addEventListener('click', closeSidebar);

    menuLinks.forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
})();

(function () {
    const searchableSelects = document.querySelectorAll('[data-searchable-select]');

    searchableSelects.forEach(function (wrapper) {
        const searchInput = wrapper.querySelector('[data-select-search]');
        const select = wrapper.querySelector('select');

        if (!searchInput || !select) {
            return;
        }

        const options = Array.from(select.options);

        const filterOptions = function () {
            const query = searchInput.value.trim().toLowerCase();

            options.forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const searchText = (option.dataset.searchText || option.textContent || '').toLowerCase();
                option.hidden = query !== '' && !searchText.includes(query) && !option.selected;
            });
        };

        searchInput.addEventListener('input', filterOptions);
        filterOptions();
    });
})();
</script>
</body>
</html>
