document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-bar');
    const resultsContainer = document.getElementById('search-results');

    const CONFIG = {
        MIN_CHARACTERS: 4,
        DEBOUNCE_DELAY: 400,
        IMAGE_BASE_URL: 'https://image.tmdb.org/t/p/w92'
    };

    let lastResults = [];
    let selectedIndex = -1;

    function debounce(func, delay) {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function attachEventListeners() {
        searchInput.addEventListener('input', debounce((e) => {
            performSearch(e.target.value.trim());
        }, CONFIG.DEBOUNCE_DELAY));

        searchInput.addEventListener('click', () => handleInputClick());
        searchInput.addEventListener('keydown', (e) => handleKeyNavigation(e));

        document.addEventListener('click', (e) => handleOutsideClick(e));
    }

    async function performSearch(query) {
        if (query.length < CONFIG.MIN_CHARACTERS) {
            hideResults();
            return;
        }

        showLoading();

        try {
            const response = await fetch(`/search_live?q=${encodeURIComponent(query)}`);

            if (!response.ok) {
                throw new Error('Une erreur est survenue.');
            }

            const data = await response.json();
            lastResults = data;
            selectedIndex = -1;

            if (data.length === 0) {
                resultsContainer.innerHTML = '<div class="p-2 text-muted">Aucun film trouvé.</div>';
            } else {
                displayResults(data);
            }
        } catch (error) {
            resultsContainer.innerHTML = `<div class="p-2 text-danger">${error.message}</div>`;
            lastResults = [];
            selectedIndex = -1;
        }
    }

    function displayResults(results) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.width = `${searchInput.offsetWidth}px`;

        results.forEach((movie, index) => {
            const resultItem = createResultItem(movie, index);
            resultsContainer.appendChild(resultItem);
        });

        resultsContainer.style.display = 'block';
    }

    function createResultItem(movie, index) {
        const item = document.createElement('a');
        item.href = `/detail/${movie.id}`;
        item.className = 'search-result-item d-flex align-items-start p-2 text-dark text-decoration-none border-bottom';
        item.dataset.index = index;

        if (movie.poster_path) {
            item.appendChild(createPosterImage(movie));
        }

        item.appendChild(createTextContent(movie));

        item.addEventListener('mouseenter', () => {
            selectedIndex = index;
            updateSelection();
        });

        return item;
    }

    function createPosterImage(movie) {
        const img = document.createElement('img');
        img.src = `${CONFIG.IMAGE_BASE_URL}${movie.poster_path}`;
        img.alt = `Poster de ${movie.title}`;
        img.className = 'me-3 rounded flex-shrink-0';
        img.style.maxHeight = '60px';
        return img;
    }

    function createTextContent(movie) {
        const container = document.createElement('div');
        container.className = 'd-flex flex-column justify-content-center text-truncate';

        const title = document.createElement('span');
        title.textContent = movie.title;
        title.className = 'fw-bold';
        container.appendChild(title);

        if (movie.release_date?.length >= 4) {
            const year = document.createElement('small');
            year.textContent = `Année : ${movie.release_date.substring(0, 4)}`;
            year.className = 'text-muted';
            container.appendChild(year);
        }

        return container;
    }

    function handleKeyNavigation(event) {
        if (!isResultsVisible()) return;

        const items = getResultItems();
        if (items.length === 0) return;

        const keyActions = {
            'ArrowDown': () => navigateDown(items.length),
            'ArrowUp': () => navigateUp(items.length),
            'Enter': () => selectCurrent(items),
            'Escape': () => hideResults()
        };

        const action = keyActions[event.key];
        if (action) {
            event.preventDefault();
            action();
        }
    }

    function navigateDown(maxLength) {
        if (selectedIndex === -1) {
            selectedIndex = 0;
        } else if (selectedIndex < maxLength - 1) {
            selectedIndex++;
        } else {
            selectedIndex = -1;
        }
        updateSelection();
    }

    function navigateUp(maxLength) {
        if (selectedIndex === 0) {
            selectedIndex = -1;
        } else if (selectedIndex === -1) {
            selectedIndex = maxLength - 1;
        } else {
            selectedIndex--;
        }
        updateSelection();
    }

    function selectCurrent(items) {
        if (selectedIndex >= 0 && selectedIndex < items.length) {
            window.location.href = items[selectedIndex].href;
        } else {
            const form = searchInput.closest('form');
            form?.submit();
            hideResults();
        }
    }

    function updateSelection() {
        const items = getResultItems();
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === selectedIndex);
            if (index === selectedIndex) {
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    }

    function handleInputClick() {
        const query = searchInput.value.trim();

        if (!isResultsVisible() && query.length >= CONFIG.MIN_CHARACTERS) {
            if (lastResults.length > 0) {
                displayResults(lastResults);
            } else {
                performSearch(query);
            }
        }
    }

    function handleOutsideClick(event) {
        if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
            hideResults();
        }
    }

    function getResultItems() {
        return Array.from(resultsContainer.querySelectorAll('.search-result-item'));
    }

    function isResultsVisible() {
        return resultsContainer.style.display === 'block';
    }

    function showLoading() {
        resultsContainer.innerHTML = '<div class="p-2 text-center text-muted">Chargement...</div>';
        resultsContainer.style.display = 'block';
    }

    function hideResults() {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        selectedIndex = -1;
    }

    attachEventListeners();
});
