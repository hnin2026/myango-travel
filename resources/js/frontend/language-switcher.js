// =========================
// SWITCH LANGUAGE
// =========================

function switchLang(lang) {

    // Save language
    localStorage.setItem('lang', lang);

    // Update current label
    const currentLang = document.getElementById('current-lang');

    if (currentLang) {
        currentLang.textContent =
            lang === 'en'
                ? 'EN'
                : 'MM';
    }

    // Update active state
    const optEn = document.getElementById('opt-en');
    const optMm = document.getElementById('opt-mm');

    if (optEn && optMm) {
        optEn.classList.toggle('active', lang === 'en');
        optMm.classList.toggle('active', lang === 'mm');
    }

    // Show/Hide content
    document.querySelectorAll('.lang-en').forEach(el => {
        el.style.display = lang === 'en' ? '' : 'none';
    });

    document.querySelectorAll('.lang-mm').forEach(el => {
        el.style.display = lang === 'mm' ? '' : 'none';
    });

    // Close dropdown
    const menu = document.getElementById('langMenu');

    if (menu) {
        menu.style.display = 'none';
    }
}

// =========================
// LANGUAGE DROPDOWN
// =========================

function toggleLangMenu() {

    const menu = document.getElementById('langMenu');

    if (!menu) return;

    menu.style.display =
        menu.style.display === 'block'
            ? 'none'
            : 'block';
}



// =========================
// CLOSE DROPDOWN OUTSIDE
// =========================

document.addEventListener('click', function (e) {

    const dropdown = document.getElementById('langDropdown');

    if (!dropdown) return;

    if (!dropdown.contains(e.target)) {

        const menu = document.getElementById('langMenu');

        if (menu) {
            menu.style.display = 'none';
        }
    }
});

// =========================
// INIT
// =========================

document.addEventListener('DOMContentLoaded', () => {

    const savedLang =
        localStorage.getItem('lang') || 'en';

    switchLang(savedLang);

});

// Make functions global
window.toggleLangMenu = toggleLangMenu;
window.switchLang = switchLang;