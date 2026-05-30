let lightboxIndex = 0;
let tourImages = [];

function initGallery(images) {
    tourImages = images;
}

function openLightbox(index) {
    lightboxIndex = index;
    updateLightboxImg();
    document.getElementById('lightbox').classList.add('show');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('show');
}

function changeLightbox(dir) {
    lightboxIndex = (lightboxIndex + dir + tourImages.length) % tourImages.length;
    updateLightboxImg();
}

function updateLightboxImg() {
    document.getElementById('lightbox-img').src = '/storage/' + tourImages[lightboxIndex];
}

document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });
    }
});