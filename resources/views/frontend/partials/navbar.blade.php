<nav class="front-navbar">
    <div class="nav-container d-flex justify-content-between align-items-center">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('images/MyanGo_Logo.png') }}"
                 alt="MyanGo Travel"
                 style="height: 50px; background: transparent;">
        </a>

        {{-- Nav Links --}}
        <div class="nav-links">
            <a href="{{ route('home') }}">Home</a>
            <a href="#tours">Tours</a>
            <a href="#contact">Contact</a>
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
        </div>
    </div>
</nav>