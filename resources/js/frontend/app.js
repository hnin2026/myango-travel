import './language-switcher.js';
import './gallery.js';
import './price-calculator.js';
import './booking.js';

// Tab switching
window.switchTab = function(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.modern-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}