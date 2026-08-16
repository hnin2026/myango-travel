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

    let dayCount = itineraryContainer.querySelectorAll('.itinerary-day').length || 1;

    addDayBtn.addEventListener('click', function () {

        const currentCount = itineraryContainer.querySelectorAll('.itinerary-day').length;
        dayCount++;

        const enId = `itinerary_en_${dayCount}`;
        const mmId = `itinerary_mm_${dayCount}`;

        const dayHtml = `
            <div class="itinerary-day card mb-3" data-day="${currentCount + 1}">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span class="fw-bold">
                        Day ${currentCount + 1}
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
                            id="${enId}"
                            name="itineraries[${currentCount}][description_en]"
                            class="form-control tinymce"
                            rows="3"
                            placeholder="Day ${currentCount + 1} details in English"
                        ></textarea>

                    </div>

                    <div class="mb-2">

                        <label class="form-label">
                            Description (Myanmar)
                        </label>

                        <textarea
                            id="${mmId}"
                            name="itineraries[${currentCount}][description_mm]"
                            class="form-control tinymce"
                            rows="3"
                            placeholder="Day ${currentCount + 1} details in Myanmar"
                        ></textarea>

                    </div>

                </div>

            </div>
        `;

        itineraryContainer.insertAdjacentHTML(
            'beforeend',
            dayHtml
        );

        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: `#${enId}, #${mmId}`,
                height: 300,
                plugins: 'lists link image table wordcount',
                toolbar:
                    'undo redo | bold italic underline | bullist numlist | link image table | removeformat',
                menubar: false,
            });
        }

    });


    // REMOVE DAY
    itineraryContainer.addEventListener('click', function (e) {

        const removeBtn =
            e.target.closest('.remove-day');

        if (!removeBtn) return;

        const dayCard = removeBtn.closest('.itinerary-day');

        if (typeof tinymce !== 'undefined') {
            const textareas = dayCard.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                if (textarea.id) {
                    const editor = tinymce.get(textarea.id);
                    if (editor) {
                        editor.remove();
                    }
                }
            });
        }

        dayCard.remove();

        document.querySelectorAll('.itinerary-day')
            .forEach((day, index) => {

                day.setAttribute('data-day', index + 1);
                day.querySelector('.fw-bold')
                    .textContent = `Day ${index + 1}`;

                const textareas = day.querySelectorAll('textarea');
                textareas.forEach(textarea => {
                    const name = textarea.getAttribute('name');
                    if (name) {
                        const newName = name.replace(/itineraries\[\d+\]/, `itineraries[${index}]`);
                        textarea.setAttribute('name', newName);
                    }
                });

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