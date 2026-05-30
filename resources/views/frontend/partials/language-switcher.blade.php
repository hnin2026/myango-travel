<!-- window.toggleLangMenu = function () {
    const menu = document.getElementById('langMenu');

    menu.style.display =
        menu.style.display === 'block' ? 'none' : 'block';
};

window.switchLang = function (lang) {

    localStorage.setItem('lang', lang);

    document.getElementById('current-lang').textContent =
        lang === 'en' ? 'EN' : 'MM';

    document.getElementById('opt-en')
        ?.classList.toggle('active', lang === 'en');

    document.getElementById('opt-mm')
        ?.classList.toggle('active', lang === 'mm');

    document.querySelectorAll('.lang-en').forEach(el => {
        el.style.display = lang === 'en' ? '' : 'none';
    });

    document.querySelectorAll('.lang-mm').forEach(el => {
        el.style.display = lang === 'mm' ? '' : 'none';
    });

    document.getElementById('langMenu').style.display = 'none';
};

document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('lang') || 'en';
    switchLang(savedLang);
});

document.addEventListener('click', function (e) {

    const dropdown = document.getElementById('langDropdown');

    if (!dropdown) return;

    if (!dropdown.contains(e.target)) {
        document.getElementById('langMenu').style.display = 'none';
    }
}); -->