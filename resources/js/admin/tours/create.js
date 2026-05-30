// TINYMCE
if (typeof tinymce !== 'undefined') {

    tinymce.init({
        selector: '.tinymce',
        height: 300,
        plugins: 'lists link image table wordcount',
        toolbar:
            'undo redo | bold italic underline | bullist numlist | link image table | removeformat',
        menubar: false,
    });

}

// TinyMCE save on submit
window.submitTourForm = function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
    }
    document.getElementById('tour-form').submit();
}

// ITINERARY DAYS
const addDayBtn =
    document.getElementById('add-day');

const itineraryContainer =
    document.getElementById('itinerary-container');

if (addDayBtn && itineraryContainer) {

    let dayCount = 1;

    addDayBtn.addEventListener('click', function () {

        dayCount++;

        const dayHtml = `
            <div class="itinerary-day card mb-3" data-day="${dayCount}">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span class="fw-bold">
                        Day ${dayCount}
                    </span>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger remove-day"
                    >
                        <i class="bi bi-trash"></i>
                        Remove
                    </button>

                </div>

                <div class="card-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Description (English)
                        </label>

                        <textarea
                            name="itineraries[${dayCount - 1}][description_en]"
                            class="form-control"
                            rows="3"
                            placeholder="Day ${dayCount} details in English"
                        ></textarea>

                    </div>

                    <div class="mb-2">

                        <label class="form-label">
                            Description (Myanmar)
                        </label>

                        <textarea
                            name="itineraries[${dayCount - 1}][description_mm]"
                            class="form-control"
                            rows="3"
                            placeholder="Day ${dayCount} details in Myanmar"
                        ></textarea>

                    </div>

                </div>

            </div>
        `;

        itineraryContainer.insertAdjacentHTML(
            'beforeend',
            dayHtml
        );

    });


    // REMOVE DAY
    itineraryContainer.addEventListener('click', function (e) {

        const removeBtn =
            e.target.closest('.remove-day');

        if (!removeBtn) return;

        removeBtn.closest('.itinerary-day').remove();

        document.querySelectorAll('.itinerary-day')
            .forEach((day, index) => {

                day.querySelector('.fw-bold')
                    .textContent = `Day ${index + 1}`;

            });

    });

}


// IMAGE PREVIEW
const imageInput =
    document.querySelector('input[name="images[]"]');

const imagePreview =
    document.getElementById('image-preview');

if (imageInput && imagePreview) {

    imageInput.addEventListener('change', function () {

        imagePreview.innerHTML = '';

        [...this.files].forEach(file => {

            const reader = new FileReader();

            reader.onload = e => {

                imagePreview.innerHTML += `
                    <div class="col-md-3 mb-2">

                        <img
                            src="${e.target.result}"
                            class="img-fluid rounded"
                            style="
                                height:120px;
                                width:100%;
                                object-fit:cover;
                            "
                        >

                    </div>
                `;

            };

            reader.readAsDataURL(file);

        });

    });

}