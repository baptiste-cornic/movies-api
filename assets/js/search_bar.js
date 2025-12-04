document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-bar');
    const resultsContainer = document.getElementById('search-results');
    const MIN_CHARACTERS = 4;
    const DEBOUNCE_DELAY = 400;
    const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w92';
    let lastResults = [];

    const debounce = (func, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    };

    const performLiveSearch = async (query) => {
        if (query.length < MIN_CHARACTERS) {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            return;
        }

        resultsContainer.innerHTML = '<div class="p-2 text-center text-muted">Chargement...</div>';
        resultsContainer.style.display = 'block';

        const url = `/search_live?q=${encodeURIComponent(query)}`;

        try {
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Erreur réseau ou code HTTP non-200');
            }

            const data = await response.json();
            lastResults = data;
            if (data.length === 0) {
                resultsContainer.innerHTML = '<div class="p-2 text-muted">Aucun film trouvé.</div>';
                return;
            }

            const searchBarWidth = searchInput.offsetWidth;
            resultsContainer.style.width = `${searchBarWidth}px`;

            displayResults(data);

        } catch (error) {
            resultsContainer.innerHTML = `<div class="p-2 text-danger">Erreur : ${error.message}</div>`;
            lastResults = [];
        }
    };

    function displayResults(results) {
        resultsContainer.innerHTML = '';

        results.forEach(movie => {
            const resultItem = document.createElement('a');
            resultItem.href = `/detail/${movie.id}`;

            resultItem.classList.add(
                'd-flex',
                'align-items-start',
                'p-2',
                'text-dark',
                'text-decoration-none',
                'border-bottom'
            );

            if (movie.poster_path) {
                const img = document.createElement('img');
                img.src = `${IMAGE_BASE_URL}${movie.poster_path}`;
                img.alt = `Poster de ${movie.title}`;
                img.classList.add('me-3', 'rounded', 'flex-shrink-0');
                img.style.maxHeight = '60px';

                resultItem.appendChild(img);
            }

            const textContainer = document.createElement('div');
            textContainer.classList.add('d-flex', 'flex-column', 'justify-content-center', 'text-truncate');

            const titleSpan = document.createElement('span');
            titleSpan.textContent = movie.title;
            titleSpan.classList.add('fw-bold');
            textContainer.appendChild(titleSpan);

            if (movie.release_date && movie.release_date.length >= 4) {
                const yearSpan = document.createElement('small');
                const year = movie.release_date.substring(0, 4);
                yearSpan.textContent = `Année : ${year}`;
                yearSpan.classList.add('text-muted');
                textContainer.appendChild(yearSpan);
            }

            resultItem.appendChild(textContainer);

            resultItem.addEventListener('mouseover', () => resultItem.classList.add('bg-light'));
            resultItem.addEventListener('mouseout', () => resultItem.classList.remove('bg-light'));

            resultsContainer.appendChild(resultItem);
        });

        resultsContainer.style.display = 'block';
    }

    searchInput.addEventListener('input', debounce((e) => {
        const query = e.target.value.trim();
        performLiveSearch(query);
    }, DEBOUNCE_DELAY));

    searchInput.addEventListener('click', () => {
        const currentQuery = searchInput.value.trim();

        if (resultsContainer.style.display === 'none' && currentQuery.length >= MIN_CHARACTERS) {
            if (lastResults && lastResults.length > 0) {
                displayResults(lastResults);
            } else if (currentQuery.length >= MIN_CHARACTERS) {
                performLiveSearch(currentQuery);
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });
});
