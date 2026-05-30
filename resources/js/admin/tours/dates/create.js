// Season preview detection

document.addEventListener('DOMContentLoaded', () => {

    // Get season periods from blade
    const seasonPeriods =
        window.seasonPeriods || [];

    const startDateInput =
        document.querySelector('input[name="start_date"]');

    if (!startDateInput) return;

    startDateInput.addEventListener('change', function () {

        const selectedDate =
            new Date(this.value);

        let detectedSeason = 'Normal';
        let badgeClass = 'warning';

        seasonPeriods.forEach(period => {

            const start =
                new Date(period.start_date);

            const end =
                new Date(period.end_date);

            let inRange = false;

            // Cross-year season support
            if (start > end) {

                inRange =
                    selectedDate >= start ||
                    selectedDate <= end;

            } else {

                inRange =
                    selectedDate >= start &&
                    selectedDate <= end;
            }

            if (inRange) {

                detectedSeason = period.name;

                if (period.season === 'peak') {

                    badgeClass = 'danger';

                } else if (period.season === 'low') {

                    badgeClass = 'success';

                } else {

                    badgeClass = 'warning';
                }
            }
        });

        const preview =
            document.getElementById('season-preview');

        const seasonName =
            document.getElementById('season-name');

        if (!preview || !seasonName) return;

        preview.style.display = 'block';

        seasonName.textContent =
            detectedSeason;

        preview.className =
            `alert alert-${badgeClass}`;
    });

});