document.addEventListener('DOMContentLoaded', function() {
    const prefix = 'cast';
    const container = document.getElementById(`${prefix}-load-more-container`);
    const button = document.getElementById(`${prefix}-load-more-button`);

    if (!button || typeof bootstrap === 'undefined' || !bootstrap.Collapse) {
        return;
    }

    const idPrefix = button.getAttribute('data-id-prefix');
    const totalFilms = parseInt(button.getAttribute('data-total-films'));
    const chunkSize = parseInt(button.getAttribute('data-chunk-size'));
    const totalChunks = parseInt(button.getAttribute('data-total-chunks'));
    let currentChunk = 1;

    button.addEventListener('click', function() {
        const targetChunk = document.getElementById(`${idPrefix}-chunk-${currentChunk}`);

        if (targetChunk) {
            bootstrap.Collapse.getOrCreateInstance(targetChunk).show();

            targetChunk.addEventListener('shown.bs.collapse', function handleShown() {
                targetChunk.removeEventListener('shown.bs.collapse', handleShown);
                currentChunk++;

                if (currentChunk < totalChunks) {
                    const remaining = totalFilms - (currentChunk * chunkSize);
                    const displayCount = Math.min(chunkSize, remaining);

                    button.setAttribute('aria-controls', `${idPrefix}-chunk-${currentChunk}`);
                    button.innerHTML = `Afficher ${displayCount} films de plus`;
                } else {
                    container.style.display = 'none';
                }
            });
        }
    });
});
