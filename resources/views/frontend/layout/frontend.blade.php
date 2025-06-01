<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Home Service | @yield('title', 'Home Service')</title>
    @vite('public/css/frontend.css')
    @vite('public/css/media.css')
</head>

<body>
    @yield('content')
    @vite('resources/js/frontend.js')
</body>

</html>
