// Initialize GLightbox when the script loads
if (typeof GLightbox !== 'undefined') {
    const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });
}
