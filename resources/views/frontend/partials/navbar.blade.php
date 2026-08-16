<nav class="front-navbar">
    <div class="nav-container d-flex justify-content-between align-items-center">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('images/MyanGo_Logo.png') }}"
                 alt="MyanGo Travel"
                 style="height: 50px; background: transparent;">
        </a>

        {{-- Nav Links --}}
        <div class="nav-links" id="navLinks">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('tours.index') }}">Tours</a>
            <div class="dropdown d-inline">
    <a href="#" class="dropdown-toggle {{ request('destination') ? 'active-dest' : '' }}" id="destinationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Destinations
    </a>
    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" aria-labelledby="destinationsDropdown">
        <li>
            <a class="dropdown-item {{ !request('destination') && request()->routeIs('tours.index') ? 'active' : '' }}" href="{{ route('tours.index') }}">
                All Destinations
            </a>
        </li>
        @foreach($navbarDestinations ?? [] as $dest)
            <li>
                <a class="dropdown-item {{ strtolower(request('destination')) === strtolower($dest) ? 'active' : '' }}" href="{{ route('tours.index', ['destination' => $dest]) }}">
                    {{ $dest }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
            <a href="{{ route('home') }}#contact">Contact</a>
        </div>

        {{-- Right Side --}}
        <div class="nav-right">
            {{-- Language Switcher --}}
            <div class="lang-dropdown" id="langDropdown">
                <button class="lang-switch" onclick="toggleLangMenu()">
                    <i class="bi bi-globe2"></i>
                    <span id="current-lang">EN</span>
                    <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                </button>
                <div class="lang-menu" id="langMenu" style="display:none;">
                    <button onclick="switchLang('en')" class="lang-option" id="opt-en">
                        🇬🇧 English
                    </button>
                    <button onclick="switchLang('mm')" class="lang-option" id="opt-mm">
                        🇲🇲 Myanmar
                    </button>
                </div>
            </div>

            {{-- Hamburger Toggler for Mobile --}}
            <button class="nav-mobile-toggle d-md-none" onclick="toggleMobileMenu()" aria-label="Toggle navigation" style="background: none; border: none; padding: 4px; display: none;">
                <i class="bi bi-list fs-3" style="color: #111844; line-height: 1;"></i>
            </button>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        var menu = document.getElementById('navLinks');
        if (menu) {
            menu.classList.toggle('show');
        }
    }
</script>