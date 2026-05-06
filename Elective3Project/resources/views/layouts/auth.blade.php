<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2583f6">
    <title>{{ $title ?? 'EveLink Login' }}</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    @if(app()->environment('production'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/css/app.css">
        <script src="/build/assets/app-CcNNqum8.js" defer></script>
    @endif
</head>
<body class="auth-body">
<main class="auth-shell">
    @yield('content')
</main>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js');
    });
}
</script>
</body>
</html>
