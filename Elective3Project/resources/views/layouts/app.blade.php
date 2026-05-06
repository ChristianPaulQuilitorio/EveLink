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
    @if(app()->environment('production'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/css/app.css">
        <script src="/build/assets/app-CcNNqum8.js" defer></script>
    @endif
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
                <div class="notification-container" id="notificationContainer">
                    <button type="button" class="topbar-bell" id="notificationBell" aria-label="View notifications">
                        &#128276;
                        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h3>Notifications</h3>
                            <div class="notification-header-actions">
                                <button type="button" class="mark-all-read" id="markAllReadBtn">Mark all as read</button>
                                <button type="button" class="delete-all-notifications" id="deleteAllNotificationsBtn">Delete all</button>
                            </div>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">Loading notifications...</div>
                        </div>
                    </div>
                </div>
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

// Notification System
(function () {
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const deleteAllNotificationsBtn = document.getElementById('deleteAllNotificationsBtn');

    if (!notificationBell) return;

    const fetchNotifications = async function () {
        try {
            const response = await fetch('{{ route("notifications.index") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();
            renderNotifications(data);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
            notificationList.innerHTML = '<div class="notification-empty">Failed to load notifications</div>';
        }
    };

    const renderNotifications = function (data) {
        const { notifications, unread_count } = data;

        if (unread_count > 0) {
            notificationBadge.textContent = unread_count;
            notificationBadge.style.display = 'inline-block';
        } else {
            notificationBadge.style.display = 'none';
        }

        if (notifications.length === 0) {
            notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
            return;
        }

        notificationList.innerHTML = notifications.map(notif => `
            <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" data-event-id="${notif.event.id}" data-notification-id="${notif.id}">
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notif.title)}</div>
                    <div class="notification-message">${escapeHtml(notif.message)}</div>
                    <div class="notification-meta">
                        <span class="event-name">${escapeHtml(notif.event.name)}</span>
                        <span class="event-date">${escapeHtml(notif.event.date)}</span>
                    </div>
                    <div class="notification-time">${escapeHtml(notif.created_at)}</div>
                </div>
                <div class="notification-actions">
                    ${!notif.is_read ? `<button type="button" class="mark-read-btn" data-notification-id="${notif.id}" aria-label="Mark as read">•</button>` : ''}
                    <button type="button" class="delete-notification-btn" data-notification-id="${notif.id}" aria-label="Delete notification">✕</button>
                </div>
            </div>
        `).join('');

        // Add click handlers to notification items
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function (e) {
                if (e.target.classList.contains('mark-read-btn') || e.target.classList.contains('delete-notification-btn')) {
                    return; // Don't navigate if clicking the read button
                }
                const eventId = this.dataset.eventId;
                const notificationId = this.dataset.notificationId;
                
                // Mark as read if unread
                if (this.classList.contains('unread')) {
                    markNotificationAsRead(notificationId);
                }
                
                // Navigate to registrations page with event selected
                window.location.href = `{{ route("registrations.index") }}?event_id=${eventId}`;
            });
        });

        // Add event listeners to mark as read buttons
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                const notificationId = btn.dataset.notificationId;
                await markNotificationAsRead(notificationId);
            });
        });

        document.querySelectorAll('.delete-notification-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                const notificationId = btn.dataset.notificationId;
                await deleteNotification(notificationId);
            });
        });
    };

    const markNotificationAsRead = async function (notificationId) {
        try {
            const response = await fetch(`{{ route("notifications.read", ":id") }}`.replace(':id', notificationId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                fetchNotifications();
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    };

    const markAllAsRead = async function () {
        try {
            const response = await fetch('{{ route("notifications.read-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                fetchNotifications();
            }
        } catch (error) {
            console.error('Failed to mark all as read:', error);
        }
    };

    const deleteNotification = async function (notificationId) {
        try {
            const response = await fetch(`{{ route("notifications.destroy", ":id") }}`.replace(':id', notificationId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                fetchNotifications();
            }
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    };

    const deleteAllNotifications = async function () {
        try {
            const response = await fetch('{{ route("notifications.destroy-all") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                fetchNotifications();
            }
        } catch (error) {
            console.error('Failed to delete all notifications:', error);
        }
    };

    const escapeHtml = function (text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Toggle dropdown
    notificationBell.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = notificationDropdown.classList.contains('is-open');
        if (isOpen) {
            notificationDropdown.classList.remove('is-open');
        } else {
            notificationDropdown.classList.add('is-open');
            fetchNotifications();
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!notificationBell.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('is-open');
        }
    });

    // Prevent dropdown from closing when clicking inside it
    notificationDropdown.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // Mark all as read button
    markAllReadBtn.addEventListener('click', function () {
        markAllAsRead();
    });

    deleteAllNotificationsBtn.addEventListener('click', function () {
        deleteAllNotifications();
    });

    // Initial fetch
    fetchNotifications();
    // Refresh every 10 seconds
    setInterval(fetchNotifications, 10000);
})();
</script>
</body>
</html>
