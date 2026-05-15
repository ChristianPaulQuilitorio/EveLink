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
        <link rel="preload" as="style" href="{{ Vite::asset('resources/css/app.css') }}">
        <link rel="preload" as="script" href="{{ Vite::asset('resources/js/app.js') }}">
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
                <span class="menu-icon" title="Dashboard" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h5A1.5 1.5 0 0 1 12 5.5v5A1.5 1.5 0 0 1 10.5 12h-5A1.5 1.5 0 0 1 4 10.5v-5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="M12.5 5.5A1.5 1.5 0 0 1 14 4h5A1.5 1.5 0 0 1 20.5 5.5v2A1.5 1.5 0 0 1 19 9h-5A1.5 1.5 0 0 1 12.5 7.5v-2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="M12.5 11.5A1.5 1.5 0 0 1 14 10h5A1.5 1.5 0 0 1 20.5 11.5v7A1.5 1.5 0 0 1 19 20h-5a1.5 1.5 0 0 1-1.5-1.5v-7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="M4 15.5A1.5 1.5 0 0 1 5.5 14h5A1.5 1.5 0 0 1 12 15.5v4A1.5 1.5 0 0 1 10.5 21h-5A1.5 1.5 0 0 1 4 19.5v-4Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">
                <span class="menu-icon" title="Events" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="6" width="16" height="14" rx="3" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M8 4v4M16 4v4M4 10h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Events</span>
            </a>
            <a href="{{ route('registrations.index') }}" class="{{ request()->routeIs('registrations.*') ? 'active' : '' }}">
                <span class="menu-icon" title="Registrations" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M4.5 19c.7-2.7 2.9-4.5 4.5-4.5s3.8 1.8 4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <path d="M14.5 8.5h5M14.5 12h5M14.5 15.5h3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Registrations</span>
            </a>
            <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <span class="menu-icon" title="Attendance" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4.5v2M18 4.5v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <rect x="4" y="6.5" width="16" height="13" rx="3" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M8 13.5l2.2 2.2L16 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
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

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

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

        notificationList.innerHTML = notifications.map(notif => {
            const eventId = notif.event?.id || '';
            const eventName = notif.event?.name || 'Event deleted';
            const eventDate = notif.event?.date || '';
            
            return `
            <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" data-event-id="${eventId}" data-notification-id="${notif.id}">
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notif.title)}</div>
                    <div class="notification-message">${escapeHtml(notif.message)}</div>
                    <div class="notification-meta">
                        <span class="event-name">${escapeHtml(eventName)}</span>
                        ${eventDate ? `<span class="event-date">${escapeHtml(eventDate)}</span>` : ''}
                    </div>
                    <div class="notification-time">${escapeHtml(notif.created_at)}</div>
                </div>
                <div class="notification-actions">
                    ${!notif.is_read ? `<button type="button" class="mark-read-btn" data-notification-id="${notif.id}" aria-label="Mark as read">•</button>` : ''}
                    <button type="button" class="delete-notification-btn" data-notification-id="${notif.id}" aria-label="Delete notification">✕</button>
                </div>
            </div>
        `;
        }).join('');

        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function (e) {
                if (e.target.classList.contains('mark-read-btn') || e.target.classList.contains('delete-notification-btn')) {
                    return;
                }
                const eventId = this.dataset.eventId;
                const notificationId = this.dataset.notificationId;

                if (!eventId) {
                    return; // Don't navigate if event was deleted
                }

                if (this.classList.contains('unread')) {
                    markNotificationAsRead(notificationId);
                }

                window.location.href = `{{ route("registrations.index") }}?event_id=${eventId}`;
            });
        });

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

    document.addEventListener('click', function (e) {
        if (!notificationBell.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('is-open');
        }
    });

    notificationDropdown.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    markAllReadBtn.addEventListener('click', function () {
        markAllAsRead();
    });

    deleteAllNotificationsBtn.addEventListener('click', function () {
        deleteAllNotifications();
    });

    fetchNotifications();
    setInterval(fetchNotifications, 10000);
})();
</script>
</body>
</html>
