// TinyMCE
if (typeof tinymce !== 'undefined') {

    tinymce.init({
        selector: '.tinymce',
        height: 300,
        plugins: 'lists link image table wordcount',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link image table | removeformat',
        menubar: false,
    });

}

// FORM SUBMIT
const tourForm = document.getElementById('tour-form');

if (tourForm) {

    tourForm.addEventListener('submit', function () {

        if (typeof tinymce !== 'undefined') {

            tinymce.triggerSave();

        }

    });

}

// REMOVE IMAGE
document.addEventListener('click', function (e) {
    const removeBtn = e.target.closest('.remove-image-btn');
    if (!removeBtn) return;

    e.preventDefault();

    if (!confirm('Remove this image?')) {
        return;
    }

    const imageId = removeBtn.getAttribute('data-image-id');
    const deleteUrl = removeBtn.getAttribute('data-delete-url');
    const imageCard = document.getElementById(`image-card-${imageId}`);
    const csrfToken = document.querySelector('input[name="_token"]').value;

    fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (imageCard) {
                imageCard.remove();
            }
            
            // If no more images, hide the card
            const remainingCards = document.querySelectorAll('.image-card');
            if (remainingCards.length === 0) {
                const currentImagesCard = document.getElementById('current-images-card');
                if (currentImagesCard) {
                    currentImagesCard.remove();
                }
            }
        } else {
            alert('Error removing image. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing image. Please try again.');
    });
});