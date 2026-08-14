import '../../css/admin/admin.css';

import './tours/create.js';
import './tours/edit.js';
import './tours/dates/create.js';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Register locationAutocomplete component
Alpine.data('locationAutocomplete', (config) => ({
    search: config.initialValue || '',
    suggestions: config.suggestions || [],
    allowNew: config.allowNew !== false, // defaults to true
    open: false,

    init() {
        this.$watch('search', value => {
            // Trigger fetchHotels if there's a match, or clear it
            const normalized = value.trim().replace(/\s+/g, ' ').toLowerCase();
            const match = this.suggestions.find(s => s.toLowerCase() === normalized);
            if (match) {
                if (window.fetchHotels) {
                    window.fetchHotels(match);
                }
            } else {
                if (window.fetchHotels) {
                    window.fetchHotels('');
                }
            }
        });
    },

    get filteredSuggestions() {
        if (!this.search) {
            return this.suggestions;
        }
        const query = this.search.toLowerCase().trim();
        return this.suggestions.filter(s => s.toLowerCase().includes(query));
    },

    get hasExactMatch() {
        const query = this.search.toLowerCase().trim();
        return this.suggestions.some(s => s.toLowerCase() === query);
    },

    selectSuggestion(suggestion) {
        this.search = suggestion;
        this.open = false;
        this.$dispatch('location-selected', { location: this.search });
        if (window.fetchHotels) {
            window.fetchHotels(this.search);
        }
    },

    addNewLocation() {
        if (!this.search.trim()) return;
        const query = this.search.trim().replace(/\s+/g, ' ');
        const existingMatch = this.suggestions.find(s => s.toLowerCase() === query.toLowerCase());
        if (existingMatch) {
            this.selectSuggestion(existingMatch);
        } else {
            const formatted = query.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
            this.suggestions.push(formatted);
            this.selectSuggestion(formatted);
        }
    }
}));

Alpine.start();

// Dynamic hotel fetching for Tour form
window.fetchHotels = function(location) {
    const container = document.getElementById('hotels-container');
    if (!container) return;

    if (!location) {
        container.innerHTML = '<p class="text-muted">Please select a location first.</p>';
        return;
    }

    container.innerHTML = `
        <div class="d-flex align-items-center gap-2 text-muted">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Loading hotels...</span>
        </div>
    `;

    fetch(`/admin/hotels-by-location?location=${encodeURIComponent(location)}`)
        .then(res => res.json())
        .then(hotels => {
            if (hotels.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        No hotels available for this location.
                        <a href="/admin/hotels/create" class="alert-link" target="_blank">Please create a hotel first.</a>
                    </div>
                `;
                return;
            }

            let html = '<div class="row">';
            hotels.forEach(hotel => {
                const isChecked = window.selectedHotels && window.selectedHotels.includes(hotel.id) ? 'checked' : '';
                const stars = '★'.repeat(hotel.stars_count);
                html += `
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input hotel-checkbox" type="checkbox"
                                   name="hotels[]" value="${hotel.id}"
                                   id="hotel${hotel.id}"
                                   ${isChecked}
                                   onchange="toggleHotelSelection(${hotel.id}, this.checked)">
                            <label class="form-check-label" for="hotel${hotel.id}">
                                <div class="fw-semibold">${hotel.name}</div>
                                <div class="text-warning small">${stars}</div>
                                <div class="text-muted small">
                                    +${hotel.price_low} (Low) | +${hotel.price_normal} (Normal) | +${hotel.price_peak} (Peak) per person
                                </div>
                            </label>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p class="text-danger">Error loading hotels.</p>';
        });
};

window.toggleHotelSelection = function(hotelId, isChecked) {
    if (!window.selectedHotels) window.selectedHotels = [];
    if (isChecked) {
        if (!window.selectedHotels.includes(hotelId)) {
            window.selectedHotels.push(hotelId);
        }
    } else {
        window.selectedHotels = window.selectedHotels.filter(id => id !== hotelId);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarClose = document.getElementById('sidebarClose');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });
    }

    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('show');
        });
    }

    // Close sidebar when clicking outside of it on mobile/tablet viewports
    document.addEventListener('click', (e) => {
        if (sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target))) {
                sidebar.classList.remove('show');
            }
        }
    });
});