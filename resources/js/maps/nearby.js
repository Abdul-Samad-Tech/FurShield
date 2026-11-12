export function startNearby(root) {
    if (!root) {
        return;
    }

    const mapHost = root.querySelector('[data-nearby-map]');
    const status = root.querySelector('[data-nearby-status]');
    const pins = readPins();
    const markers = new Map();
    let map = null;
    let leaflet = null;

    root.querySelectorAll('[data-nearby-focus]').forEach((button) => {
        button.addEventListener('click', () => {
            select(button.getAttribute('data-nearby-focus'), true);
        });
    });

    root.querySelector('[data-nearby-locate]')?.addEventListener('click', () => {
        askForLocation(true);
    });

    const params = new URLSearchParams(window.location.search);

    if (!params.get('latitude') && !params.get('city') && sessionStorage.getItem('nearby-geo') !== 'denied') {
        askForLocation(false);
    }

    if (pins.length === 0) {
        showPlaceholder('No clinics with a map pin match these filters.');
        return;
    }

    const load = () => {
        import('leaflet')
            .then(async (mod) => {
                await import('leaflet/dist/leaflet.css');
                leaflet = mod.default ?? mod;
                draw();
            })
            .catch(() => {
                showPlaceholder('');
                const error = mapHost?.querySelector('[data-nearby-error]');
                if (error) {
                    error.classList.remove('hidden');
                    error.classList.add('grid');
                }
            });
    };

    if (!mapHost || !('IntersectionObserver' in window)) {
        load();
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) {
            return;
        }

        observer.disconnect();
        load();
    }, { rootMargin: '240px' });

    observer.observe(mapHost);

    function readPins() {
        const source = document.getElementById('nearby-pins');

        if (!source) {
            return [];
        }

        try {
            const parsed = JSON.parse(source.textContent);

            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    function draw() {
        const canvas = mapHost.querySelector('[data-nearby-canvas]');
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        map = leaflet.map(canvas, {
            scrollWheelZoom: false,
            zoomAnimation: !reduce,
            fadeAnimation: !reduce,
            markerZoomAnimation: !reduce,
        });

        leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);

        const bounds = [];

        pins.forEach((pin) => {
            const marker = leaflet.marker([pin.latitude, pin.longitude], {
                icon: pinIcon(false),
                keyboard: true,
                title: pin.name,
            }).addTo(map);

            marker.bindPopup(`<strong>${escapeHtml(pin.name)}</strong><br><a href="${escapeHtml(pin.url)}">View clinic</a>`);
            marker.on('click', () => select(pin.id, false));
            markers.set(String(pin.id), marker);
            bounds.push([pin.latitude, pin.longitude]);
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], 13, { animate: false });
        } else {
            map.fitBounds(bounds, { padding: [28, 28], animate: false });
        }

        showPlaceholder('');
        mapHost.setAttribute('data-nearby-ready', 'true');
    }

    function pinIcon(selected) {
        return leaflet.divIcon({
            className: '',
            html: `<span class="nearby-pin${selected ? ' is-selected' : ''}"></span>`,
            iconSize: selected ? [24, 24] : [20, 20],
            iconAnchor: selected ? [12, 12] : [10, 10],
        });
    }

    function select(id, pan) {
        const key = String(id);
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        root.querySelectorAll('[data-vet-id]').forEach((card) => {
            const on = card.getAttribute('data-vet-id') === key;

            if (on) {
                card.dataset.selected = 'true';
                card.scrollIntoView({ block: 'nearest', behavior: reduce ? 'auto' : 'smooth' });
            } else {
                delete card.dataset.selected;
            }
        });

        markers.forEach((marker, markerId) => {
            marker.setIcon(pinIcon(markerId === key));
        });

        const marker = markers.get(key);

        if (!marker || !map) {
            return;
        }

        if (pan) {
            map.setView(marker.getLatLng(), Math.max(map.getZoom(), 13), { animate: !reduce });
        }

        marker.openPopup();
    }

    function askForLocation(fromButton) {
        if (!navigator.geolocation) {
            if (status) {
                status.textContent = 'This browser cannot share a location. Search by city instead.';
            }
            root.querySelector('#vet-city')?.focus();
            return;
        }

        if (status) {
            status.textContent = 'Finding your location…';
        }

        navigator.geolocation.getCurrentPosition((position) => {
            const url = new URL(window.location.href);
            url.searchParams.set('latitude', Number(position.coords.latitude).toFixed(6));
            url.searchParams.set('longitude', Number(position.coords.longitude).toFixed(6));

            if (fromButton) {
                url.searchParams.delete('city');
            }

            window.location.assign(url.toString());
        }, () => {
            sessionStorage.setItem('nearby-geo', 'denied');

            if (status) {
                status.textContent = 'Location is unavailable. Search by city to see clinics there.';
            }

            root.querySelector('#vet-city')?.focus();
        }, { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 });
    }

    function showPlaceholder(message) {
        const placeholder = mapHost?.querySelector('[data-nearby-placeholder]');

        if (!placeholder) {
            return;
        }

        if (message === '') {
            placeholder.classList.add('hidden');
            placeholder.classList.remove('grid');
            return;
        }

        const copy = placeholder.querySelector('p');

        if (copy) {
            copy.textContent = message;
        }
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[character]);
    }
}
