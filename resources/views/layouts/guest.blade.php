<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MyanGo Travel') }}</title>

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- Admin Assets --}}
    @vite([
        'resources/js/admin/admin.js'
    ])
</head>

<body>

    <div class="auth-wrapper">

        <div class="auth-card">

            <div class="text-center mb-4">

                <img
                    src="{{ asset('images/MyanGo_Logo.png') }}"
                    alt="MyanGo Travel"
                    style="height: 70px;"
                >

            </div>

            {{ $slot }}

        </div>

    </div>

</body>
</html>