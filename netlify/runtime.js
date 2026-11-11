(function () {
    const apiBase = window.ARTISAN_API_BASE_URL;

    if (!apiBase) {
        console.error('ARTISAN_API_BASE_URL is not configured.');
        return;
    }

    const nativeFetch = window.fetch.bind(window);
    const routeId = (() => {
        const patterns = [
            /^\/catalogue\/oeuvres\/([^/]+)/,
            /^\/catalogue\/artisans\/([^/]+)/,
            /^\/artisan\/oeuvres\/([^/]+)\/edit$/,
        ];

        for (const pattern of patterns) {
            const match = window.location.pathname.match(pattern);
            if (match) return match[1];
        }

        return null;
    })();

    window.fetch = function (input, options) {
        const requestUrl = typeof input === 'string' ? input : input.url;
        const url = new URL(requestUrl, window.location.href);

        if (url.origin === window.location.origin && url.pathname === '/sanctum/csrf-cookie') {
            return Promise.resolve(new Response(null, { status: 204 }));
        }

        const isLocalApiRequest = url.origin === window.location.origin
            && (url.pathname.startsWith('/api/') || url.pathname.startsWith('/storage/'));

        if (isLocalApiRequest) {
            const apiUrl = new URL(`${url.pathname}${url.search}${url.hash}`, apiBase);

            if (routeId) {
                apiUrl.pathname = apiUrl.pathname.replaceAll('42424242', encodeURIComponent(routeId));
            }

            const headers = new Headers(options?.headers ?? (input instanceof Request ? input.headers : undefined));
            headers.delete('X-XSRF-TOKEN');

            if (
                url.pathname.startsWith('/api/')
                && !/\/auth\/(?:login|register)$/.test(url.pathname)
                && !headers.has('Authorization')
            ) {
                const token = window.localStorage.getItem('token');
                if (token) headers.set('Authorization', `Bearer ${token}`);
            }

            return nativeFetch(apiUrl.href, {
                ...options,
                headers,
                credentials: 'omit',
            });
        }

        return nativeFetch(input, options);
    };

    function normalizeStorageImages(root) {
        root.querySelectorAll?.('img[src^="/storage/"]').forEach((image) => {
            image.src = new URL(image.getAttribute('src'), apiBase).href;
        });
    }

    normalizeStorageImages(document);
    new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            if (mutation.type === 'attributes' && mutation.target instanceof HTMLImageElement) {
                normalizeStorageImages(mutation.target.parentElement ?? document);
            }

            mutation.addedNodes.forEach((node) => {
                if (node instanceof Element) {
                    if (node.matches('img[src^="/storage/"]')) {
                        node.src = new URL(node.getAttribute('src'), apiBase).href;
                    }
                    normalizeStorageImages(node);
                }
            });
        }
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['src'],
        childList: true,
        subtree: true,
    });

    function loadFeaturedArtisans() {
        nativeFetch(new URL('/api/v1/catalog/artisans?per_page=3', apiBase), {
            headers: { Accept: 'application/json' },
        })
            .then((response) => {
                if (!response.ok) throw new Error(`API responded with ${response.status}`);
                return response.json();
            })
            .then((json) => {
                const cards = document.querySelector('.section .cards');
                const artisans = Array.isArray(json.data) ? json.data : [];
                if (!cards || !artisans.length) return;

                cards.replaceChildren(...artisans.map((artisan) => {
                    const card = document.createElement('article');
                    card.className = 'artisan-home-card card-animate';

                    const avatar = document.createElement('div');
                    avatar.className = 'artisan-home-avatar';
                    avatar.textContent = artisan.name?.slice(0, 1).toUpperCase() ?? 'A';

                    const name = document.createElement('div');
                    name.className = 'artisan-home-name';
                    name.textContent = artisan.name ?? 'Artisan';

                    const description = document.createElement('p');
                    description.style.cssText = 'color:var(--text-mid);font-size:0.88rem;line-height:1.6';
                    description.textContent = artisan.bio ?? 'Artisan talentueux sur ArtisanConnect.';

                    const link = document.createElement('a');
                    link.className = 'card-link';
                    link.href = `/catalogue/artisans/${encodeURIComponent(artisan.id)}`;
                    link.textContent = 'Voir le profil →';

                    card.append(avatar, name);
                    if (artisan.specialite) {
                        const specialty = document.createElement('span');
                        specialty.className = 'artisan-tag';
                        specialty.textContent = artisan.specialite;
                        card.append(specialty);
                    }
                    card.append(description, link);
                    return card;
                }));
            })
            .catch((error) => console.error('Impossible de charger les artisans à la une.', error));
    }

    if (window.location.pathname === '/') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadFeaturedArtisans, { once: true });
        } else {
            loadFeaturedArtisans();
        }
    }
})();
