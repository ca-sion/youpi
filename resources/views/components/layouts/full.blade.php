<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">

        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {!! SEO::generate() !!}

        <link rel="icon" type="image/png" sizes="512x512" href="/images/logo-youpi.png">
        <link rel="icon" href="/images/logo-youpi.svg">

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite('resources/css/app.css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        @stack('head')
    </head>

    <body class="antialiased bg-gray-50 dark:bg-gray-900">

        <main class="mx-auto h-auto">
        {{ $slot }}
        </main>

        @stack('modal')
        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.0/flowbite.min.js"></script>
        @stack('bottom')
    </body>
</html>
