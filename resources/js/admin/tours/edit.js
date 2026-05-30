document.addEventListener('DOMContentLoaded', () => {

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
    const form = document.querySelector('form');

    if (form) {

        form.addEventListener('submit', function () {

            if (typeof tinymce !== 'undefined') {

                tinymce.triggerSave();

            }

        });

    }

});