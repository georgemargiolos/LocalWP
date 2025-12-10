/**
 * Scroll handler for yacht location links
 * Scrolls to map section when ?scroll=map is in URL
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if scroll parameter is present
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('scroll') === 'map') {
        // Wait for page to fully load
        setTimeout(function() {
            var mapSection = document.querySelector('.yacht-map-section');
            if (mapSection) {
                mapSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Remove scroll parameter from URL without reload
                var newUrl = window.location.pathname + window.location.search.replace(/[?&]scroll=map/, '').replace(/^&/, '?');
                if (newUrl.endsWith('?')) {
                    newUrl = newUrl.slice(0, -1);
                }
                window.history.replaceState({}, '', newUrl);
            }
        }, 800);
    }
});
