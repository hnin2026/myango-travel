<nav class="admin-sidebar">

    {{-- LOGO --}}
    <div class="sidebar-logo">

        <a href="{{ route('dashboard') }}"
           class="logo-link">

            <img
                src="{{ asset('images/MyanGo_Logo.png') }}"
                alt="MyanGo Travel"
                class="logo-img"
            >

        </a>

    </div>

    {{-- MENU --}}
    <div class="sidebar-menu">

        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>

        </a>

        <a href="{{ route('admin.tours.index') }}"
           class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">

            <i class="bi bi-airplane-fill"></i>
            <span>Tours</span>

        </a>

        <a href="{{ route('admin.hotels.index') }}"
           class="{{ request()->routeIs('admin.hotels.*') ? 'active' : '' }}">

            <i class="bi bi-building-fill"></i>
            <span>Hotels</span>

        </a>

        <a href="#">

            <i class="bi bi-calendar-check-fill"></i>
            <span>Bookings</span>

        </a>

        <a href="#">

            <i class="bi bi-chat-dots-fill"></i>
            <span>Inquiries</span>

        </a>

    </div>

    {{-- USER --}}
    <div class="sidebar-user">

        <div class="user-box">

            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <div class="user-info">

                <div class="user-name">
                    {{ Auth::user()->name }}
                </div>

                <div class="user-role">
                    Administrator
                </div>

            </div>

        </div>

        <form method="POST"
              action="{{ route('logout') }}">

            @csrf

            <button type="submit"
                    class="logout-btn">

                <i class="bi bi-box-arrow-right"></i>
                Logout

            </button>

        </form>

    </div>

</nav>