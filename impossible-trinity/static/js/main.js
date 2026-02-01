// View toggle functionality
function switchView(viewType) {
    const url = new URL(window.location);
    url.searchParams.set('view', viewType);
    window.location.href = url.toString();
}

// Table row creation helper
function createTableRow(item) {
    const tr = document.createElement('tr');
    tr.setAttribute('role', 'link');
    tr.setAttribute('tabindex', '0');
    tr.setAttribute('aria-label', `查看 ${item.name}`);
    tr.onclick = () => location.href = `/detail/${item.id}`;
    tr.onkeydown = (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            location.href = `/detail/${item.id}`;
        }
    };

    const nameCell = document.createElement('td');
    nameCell.className = 'table-name';
    nameCell.textContent = item.name;

    const fieldCell = document.createElement('td');
    fieldCell.textContent = item.field;

    const element1Cell = document.createElement('td');
    element1Cell.textContent = item.element1;

    const element2Cell = document.createElement('td');
    element2Cell.textContent = item.element2;

    const element3Cell = document.createElement('td');
    element3Cell.textContent = item.element3;

    const agreeCell = document.createElement('td');
    agreeCell.textContent = item.agree_count;

    const commentsCell = document.createElement('td');
    commentsCell.textContent = item.comments_count;

    const dateCell = document.createElement('td');
    const date = new Date(item.created_at);
    dateCell.textContent = date.toISOString().split('T')[0];

    tr.appendChild(nameCell);
    tr.appendChild(fieldCell);
    tr.appendChild(element1Cell);
    tr.appendChild(element2Cell);
    tr.appendChild(element3Cell);
    tr.appendChild(agreeCell);
    tr.appendChild(commentsCell);
    tr.appendChild(dateCell);

    return tr;
}

// Auto-hide flash messages
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 300);
        }, 5000);
    });

    const cardGrid = document.getElementById('card-grid');
    if (cardGrid) {
        const sentinel = document.getElementById('card-sentinel');
        const loader = document.getElementById('card-loader');
        let page = Number(cardGrid.dataset.page || '1');
        let hasMore = cardGrid.dataset.hasMore === 'true';
        const perPage = Number(cardGrid.dataset.perPage || '12');
        let isLoading = false;

        const createBadge = (label, explanation) => {
            const badge = document.createElement('div');
            badge.className = 'element-badge tooltip-container';
            const text = document.createElement('span');
            text.textContent = label;
            badge.appendChild(text);

            if (explanation) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip-content card-tooltip';
                const strong = document.createElement('strong');
                strong.textContent = `${label}无法满足时：`;
                tooltip.appendChild(strong);
                tooltip.appendChild(document.createTextNode(explanation));
                badge.appendChild(tooltip);
            }

            return badge;
        };

        const createCard = (item) => {
            const card = document.createElement('a');
            card.className = 'card card-link masonry-item';
            card.href = `/detail/${item.id}`;

            const content = document.createElement('div');
            content.className = 'card-content';

            const headerRow = document.createElement('div');
            headerRow.className = 'card-header-row';

            const title = document.createElement('h3');
            title.className = 'card-title';
            title.textContent = item.name;

            const field = document.createElement('p');
            field.className = 'card-field';
            field.textContent = item.field;

            headerRow.appendChild(title);
            headerRow.appendChild(field);

            const elements = document.createElement('div');
            elements.className = 'card-elements-horizontal';
            elements.appendChild(createBadge(item.element1, item.element1_sacrifice_explanation));
            elements.appendChild(createBadge(item.element2, item.element2_sacrifice_explanation));
            elements.appendChild(createBadge(item.element3, item.element3_sacrifice_explanation));

            const preview = document.createElement('p');
            preview.className = 'card-preview';
            const trimmed = item.description && item.description.length > 150;
            preview.textContent = trimmed ? `${item.description.slice(0, 150)}...` : (item.description || '');

            const meta = document.createElement('div');
            meta.className = 'card-meta';

            const agree = document.createElement('span');
            agree.className = 'agree-count';
            const agreeIcon = document.createElement('span');
            agreeIcon.className = 'meta-icon';
            agreeIcon.textContent = '赞';
            agree.appendChild(agreeIcon);
            agree.appendChild(document.createTextNode(item.agree_count));

            const comments = document.createElement('span');
            comments.className = 'comment-count';
            const commentIcon = document.createElement('span');
            commentIcon.className = 'meta-icon';
            commentIcon.textContent = '评';
            comments.appendChild(commentIcon);
            comments.appendChild(document.createTextNode(item.comments_count));

            const cta = document.createElement('span');
            cta.className = 'card-cta';
            cta.textContent = '查看详情 →';

            meta.appendChild(agree);
            meta.appendChild(comments);
            meta.appendChild(cta);

            content.appendChild(headerRow);
            content.appendChild(elements);
            content.appendChild(preview);
            content.appendChild(meta);
            card.appendChild(content);

            return card;
        };

        const loadMore = async () => {
            if (isLoading || !hasMore) {
                return;
            }
            isLoading = true;
            if (loader) {
                loader.classList.add('active');
            }

            const nextPage = page + 1;
            try {
                const response = await fetch(`/api/its?page=${nextPage}&per_page=${perPage}`);
                if (!response.ok) {
                    throw new Error('Failed to load cards');
                }
                const data = await response.json();
                data.items.forEach(item => {
                    cardGrid.appendChild(createCard(item));
                });
                page = nextPage;
                hasMore = data.has_more;
                cardGrid.dataset.page = String(page);
                cardGrid.dataset.hasMore = hasMore ? 'true' : 'false';
                if (!hasMore && sentinel) {
                    sentinel.style.display = 'none';
                }
            } catch (error) {
                console.error(error);
            } finally {
                isLoading = false;
                if (loader) {
                    loader.classList.remove('active');
                }
            }
        };

        if (sentinel && hasMore) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    loadMore();
                }
            }, { rootMargin: '200px' });
            observer.observe(sentinel);
        }
    }

    // Table view lazy loading
    const tableContainer = document.getElementById('table-container');
    if (tableContainer) {
        const tableBody = document.getElementById('table-body');
        const tableSentinel = document.getElementById('table-sentinel');
        const tableLoader = document.getElementById('table-loader');
        let tablePage = Number(tableContainer.dataset.page || '1');
        let tableHasMore = tableContainer.dataset.hasMore === 'true';
        const tablePerPage = Number(tableContainer.dataset.perPage || '20');
        let tableIsLoading = false;

        const loadMoreTable = async () => {
            if (tableIsLoading || !tableHasMore) {
                return;
            }
            tableIsLoading = true;
            if (tableLoader) {
                tableLoader.classList.add('active');
            }

            const nextPage = tablePage + 1;
            try {
                const response = await fetch(`/api/its?page=${nextPage}&per_page=${tablePerPage}`);
                if (!response.ok) {
                    throw new Error('Failed to load table rows');
                }
                const data = await response.json();
                data.items.forEach(item => {
                    tableBody.appendChild(createTableRow(item));
                });
                tablePage = nextPage;
                tableHasMore = data.has_more;
                tableContainer.dataset.page = String(tablePage);
                tableContainer.dataset.hasMore = tableHasMore ? 'true' : 'false';
                if (!tableHasMore && tableSentinel) {
                    tableSentinel.style.display = 'none';
                }
            } catch (error) {
                console.error(error);
            } finally {
                tableIsLoading = false;
                if (tableLoader) {
                    tableLoader.classList.remove('active');
                }
            }
        };

        if (tableSentinel && tableHasMore) {
            const tableObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    loadMoreTable();
                }
            }, { rootMargin: '200px' });
            tableObserver.observe(tableSentinel);
        }
    }
});

// Form validation helpers
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.background = '#f8d7da';
        } else {
            input.style.background = '#f8f8f8';
        }
    });
    
    return isValid;
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
