// View toggle functionality
function switchView(viewType) {
    const url = new URL(window.location);
    url.searchParams.set('view', viewType);
    window.location.href = url.toString();
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

            const title = document.createElement('h3');
            title.className = 'card-title';
            title.textContent = item.name;

            const field = document.createElement('p');
            field.className = 'card-field';
            field.textContent = item.field;

            const elements = document.createElement('div');
            elements.className = 'card-elements-horizontal';
            elements.appendChild(createBadge(item.element1, item.element1_sacrifice_explanation));
            elements.appendChild(createBadge(item.element2, item.element2_sacrifice_explanation));
            elements.appendChild(createBadge(item.element3, item.element3_sacrifice_explanation));

            const preview = document.createElement('p');
            preview.className = 'card-preview';
            const trimmed = item.description && item.description.length > 100;
            preview.textContent = trimmed ? `${item.description.slice(0, 100)}...` : (item.description || '');

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

            content.appendChild(title);
            content.appendChild(field);
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
