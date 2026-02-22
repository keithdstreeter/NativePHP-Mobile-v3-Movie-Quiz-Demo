<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="theme-color" content="#7c3aed">

        <title>{{ $title ?? config('app.name', 'Quiz App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased bg-gradient-to-b from-ocean-50 via-candy-50 to-sunny-50 text-gray-900">
        <div class="fixed top-2 right-2 z-50">
            <livewire:network-status-indicator />
        </div>
        {{ $slot }}
    </body>
</html>
