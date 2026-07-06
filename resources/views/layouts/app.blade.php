<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'MyanGo Travel Admin') }}</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- Admin JS/CSS --}}
    @vite([
        'resources/js/admin/admin.js'
    ])
</head>

<body>

    <div class="sidebar">

        <div class="sidebar-logo">
            <img src="{{ asset('images/MyanGo_Logo.png') }}"
                 alt="MyanGo Travel"
                 style="height: 50px;">
        </div>

        <div class="sidebar-subtitle">
            Travel Admin Panel
        </div>

        <div class="sidebar-menu">

            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i>
                Dashboard
            </a>

            <a href="{{ route('admin.tours.index') }}"
               class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">
               <i class="bi bi-geo-alt-fill"></i>
               Tours
            </a>

            <a href="{{ route('admin.hotels.index') }}"
               class="{{ request()->routeIs('admin.hotels.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                Hotels
            </a>

            <a href="{{ route('admin.season-periods.index') }}"
               class="{{ request()->routeIs('admin.season-periods.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i>
                Seasons
            </a>

            <a href="{{ route('admin.bookings.index') }}"
               class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                Bookings
            </a>

            <a href="{{ route('admin.inquiries.index') }}"
               class="{{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i>
                Inquiries
            </a>

            <hr style="border-color: rgba(255,255,255,0.12);">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        style="
                            width:100%;
                            border:none;
                            background:none;
                            color:rgba(255,255,255,0.75);
                            padding:14px 18px;
                            text-align:left;
                            border-radius:14px;
                            transition:0.3s;
                            font-weight:500;
                        "
                        onmouseover="this.style.background='rgba(255,255,255,0.12)'"
                        onmouseout="this.style.background='transparent'">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            </form>

        </div>
    </div>

    <div class="admin-main">

        @if (isset($header))
            <div class="page-header">
                <div>
                    {{ $header }}
                </div>
            </div>
        @endif
        <main>
            {{ $slot }}
        </main>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>