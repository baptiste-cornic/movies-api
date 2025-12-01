
document.addEventListener('DOMContentLoaded', function() {
    function initCarousel(id) {
        const track = document.querySelector(`[data-carousel-track="${id}"]`);
        const container = document.querySelector(`[data-carousel-container="${id}"]`);
        const buttons = document.querySelectorAll(`[data-carousel="${id}"]`);

        const itemWidth = 200 + 16; // width + gap
        const itemsPerPage = Math.floor(container.offsetWidth / itemWidth);
        const totalPages = Math.ceil(track.children.length / itemsPerPage);
        let currentPage = 0;

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = btn.dataset.next ?
                    (currentPage + 1) % totalPages :
                    (currentPage - 1 + totalPages) % totalPages;
                track.style.transform = `translateX(-${currentPage * itemsPerPage * itemWidth}px)`;
            });
        });
    }

    initCarousel('popular');
    initCarousel('toprated');
});
