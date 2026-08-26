<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/img/logo-aiti.svg') }}" type="image/x-icon" />

    @viteReactRefresh
    @vite('resources/js/inertia.tsx')
    @inertiaHead
</head>

<body class="h-full font-sans antialiased">
    @inertia
</body>

</html>
