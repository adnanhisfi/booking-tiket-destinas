<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JelajahKu')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { primary: '#2E7D32', soft: '#E8F5E9' },
            fontFamily: { sans: ['Poppins', 'Inter', 'sans-serif'] }
        } } }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('styles')
</head>
<body class="font-sans text-slate-800 bg-gray-100">
    @yield('body')
    @stack('scripts')
</body>
</html>
