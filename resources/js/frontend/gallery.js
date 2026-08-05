let lightboxIndex = 0;
let tourImages = [];

function initGallery(images) {
    tourImages = images;
}

function openLightbox(index) {
    tourImages = window.tourImages || [];
    lightboxIndex = index;
    updateLightboxImg();
    document.getElementById('lightbox').classList.add('show');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('show');
}

function changeLightbox(dir) {
    tourImages = window.tourImages || [];
    lightboxIndex = (lightboxIndex + dir + tourImages.length) % tourImages.length;
    updateLightboxImg();
}

function updateLightboxImg() {
    document.getElementById('lightbox-img').src = '/storage/' + tourImages[lightboxIndex];
}

window.initGallery = initGallery;
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;
window.changeLightbox = changeLightbox;

document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });
    }
});