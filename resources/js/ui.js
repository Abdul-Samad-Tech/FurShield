import { enterPanel, leavePanel } from './motion/panels';
import { registerAdoptDeck } from './adopt-deck';
import {
    createIcons,
    Activity,
    Bell,
    Bird,
    Bone,
    Calendar,
    Cat,
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CircleAlert,
    CircleCheck,
    Dog,
    Heart,
    HeartPulse,
    House,
    Inbox,
    Info,
    LayoutDashboard,
    Loader,
    LogOut,
    MapPin,
    Menu,
    Navigation,
    PawPrint,
    Pencil,
    Plus,
    Receipt,
    BookOpen,
    Search,
    Shield,
    ShoppingBag,
    ShoppingCart,
    Sparkles,
    Stethoscope,
    Trash2,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    User,
    X,
    Download,
    MessageCircle,
    Phone,
    QrCode,
} from 'lucide';

const icons = {
    Activity,
    Bell,
    Bird,
    Bone,
    Calendar,
    Cat,
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CircleAlert,
    CircleCheck,
    Dog,
    Download,
    Heart,
    HeartPulse,
    House,
    Inbox,
    Info,
    LayoutDashboard,
    Loader,
    LogOut,
    MapPin,
    Menu,
    MessageCircle,
    Navigation,
    PawPrint,
    Pencil,
    Phone,
    Plus,
    QrCode,
    Receipt,
    BookOpen,
    Search,
    Shield,
    ShoppingBag,
    ShoppingCart,
    Sparkles,
    Stethoscope,
    Trash2,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    User,
    X,
};

const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

export function registerUi(Alpine) {
    Alpine.data('fsModal', () => ({
        shown: false,
        previous: null,
        open() {
            if (this.shown) {
                return;
            }

            this.previous = document.activeElement;
            this.shown = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    const first = this.focusables()[0];
                    (first || this.$refs.dialog)?.focus();
                });
            });
        },
        close() {
            if (!this.shown) {
                return;
            }

            this.shown = false;
            document.body.classList.remove('overflow-hidden');

            if (this.previous instanceof HTMLElement && document.contains(this.previous)) {
                this.previous.focus();
            }
        },
        focusables() {
            if (!this.$refs.dialog) {
                return [];
            }

            return [...this.$refs.dialog.querySelectorAll(focusableSelector)].filter((element) => {
                return element.getAttribute('tabindex') !== '-1' && element.getClientRects().length > 0;
            });
        },
        trap(event) {
            if (!this.shown || event.key !== 'Tab') {
                return;
            }

            const items = this.focusables();

            if (items.length === 0) {
                event.preventDefault();
                this.$refs.dialog?.focus();
                return;
            }

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('fsToasts', (initial = []) => ({
        toasts: [],
        init() {
            this.toasts = initial.filter((toast) => toast.message).map((toast) => this.decorate(toast));
            this.toasts.forEach((toast) => this.schedule(toast));
            window.addEventListener('fs-toast', (event) => {
                const toast = this.decorate(event.detail || {});
                if (!toast.message) {
                    return;
                }
                this.toasts.push(toast);
                this.schedule(toast);
            });
        },
        decorate(toast) {
            return {
                id: toast.id || `toast-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                message: toast.message || '',
                variant: toast.variant || 'success',
                action: toast.action || '',
                event: toast.event || '',
                detail: toast.detail ?? null,
            };
        },
        schedule(toast) {
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const delay = toast.action ? 8000 : (reduced ? 6000 : 4200);
            window.setTimeout(() => this.dismiss(toast.id), delay);
        },
        act(toast) {
            if (toast.event) {
                window.dispatchEvent(new CustomEvent(toast.event, { detail: toast.detail }));
            }
            this.dismiss(toast.id);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
    }));

    Alpine.data('fsTabs', (initial) => ({
        active: initial,
        motionToken: 0,
        select(id) {
            if (this.active === id) {
                return;
            }

            const root = this.$root;
            const current = [...root.querySelectorAll('[role="tabpanel"]')].find((panel) => panel.offsetParent !== null);
            const token = this.motionToken + 1;
            this.motionToken = token;

            leavePanel(current).then(() => {
                if (this.motionToken !== token) {
                    return;
                }

                this.active = id;
                this.$nextTick(() => {
                    if (this.motionToken !== token) {
                        return;
                    }

                    enterPanel(root.querySelector(`#fs-panel-${CSS.escape(id)}`));
                    root.querySelector(`[data-tab="${CSS.escape(id)}"]`)?.focus();
                });
            });
        },
        onKey(event) {
            const tabs = [...this.$root.querySelectorAll('[role="tab"]:not([disabled])')];
            const index = tabs.findIndex((tab) => tab.dataset.tab === this.active);

            if (index === -1 || tabs.length === 0) {
                return;
            }

            let next = index;

            if (event.key === 'ArrowRight') {
                next = index === tabs.length - 1 ? 0 : index + 1;
            } else if (event.key === 'ArrowLeft') {
                next = index === 0 ? tabs.length - 1 : index - 1;
            } else if (event.key === 'Home') {
                next = 0;
            } else if (event.key === 'End') {
                next = tabs.length - 1;
            } else {
                return;
            }

            this.select(tabs[next].dataset.tab);
        },
    }));

    Alpine.data('fsShell', () => ({
        drawer: false,
        menu: false,
        openDrawer() {
            this.menu = false;
            this.drawer = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.drawerPanel?.focus());
        },
        closeDrawer() {
            this.drawer = false;
            if (!this.$store.palette.open && !this.$store.cart.open) {
                document.body.classList.remove('overflow-hidden');
            }
            this.$refs.moreTab?.focus();
        },
        onEscape() {
            if (this.$store.palette.open || this.$store.cart.open) {
                return;
            }
            if (this.menu) {
                this.menu = false;
                return;
            }
            if (this.drawer) {
                this.closeDrawer();
            }
        },
        trap(event, panel) {
            if (!panel || event.key !== 'Tab') {
                return;
            }

            const items = [...panel.querySelectorAll(focusableSelector)].filter((element) => element.getClientRects().length > 0);
            if (items.length === 0) {
                event.preventDefault();
                panel.focus();
                return;
            }

            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('petFound', () => ({
        latitude: '',
        longitude: '',
        locating: false,
        locationNote: '',
        shareLocation() {
            if (!navigator.geolocation) {
                this.locationNote = 'This browser cannot share a location. You can still send the note.';
                return;
            }

            this.locating = true;
            this.locationNote = 'Asking the browser for a location…';
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.latitude = Number(position.coords.latitude).toFixed(6);
                    this.longitude = Number(position.coords.longitude).toFixed(6);
                    this.locationNote = 'Location attached. It is sent only if you submit the form.';
                    this.locating = false;
                },
                () => {
                    this.latitude = '';
                    this.longitude = '';
                    this.locationNote = 'Location was not shared. You can still send the note.';
                    this.locating = false;
                },
                { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 },
            );
        },
    }));

    Alpine.data('bookingWizard', () => ({
        ready: false,
        step: 1,
        petId: '',
        vetId: '',
        date: '',
        time: '',
        city: '',
        specialty: '',
        reason: '',
        notes: '',
        locked: false,
        data: { vets: [], initial: {} },
        init() {
            const source = document.getElementById('booking-data');
            this.data = source ? JSON.parse(source.textContent) : { vets: [], initial: {} };
            const initial = this.data.initial || {};
            this.locked = Boolean(this.data.locked);
            this.petId = String(initial.pet_id || '');
            this.vetId = String(initial.vet_id || '');
            this.reason = initial.reason || '';
            this.notes = initial.notes || '';
            this.time = initial.time || '';
            this.step = Number(initial.step || 1);
            if (this.time.includes('T')) {
                this.date = this.time.slice(0, 10);
            } else if (this.selectedVet()?.calendar?.earliest) {
                this.date = this.selectedVet().calendar.earliest.date;
            }
            this.ready = true;
        },
        selectedVet() {
            return this.data.vets.find((vet) => String(vet.id) === String(this.vetId)) || null;
        },
        filteredVets() {
            const city = this.city.trim().toLowerCase();
            const specialty = this.specialty.trim().toLowerCase();

            return this.data.vets.filter((vet) => {
                const cityOk = city === '' || String(vet.city || '').toLowerCase().includes(city);
                const specialtyOk = specialty === '' || String(vet.specialization || '').toLowerCase().includes(specialty);

                return cityOk && specialtyOk;
            });
        },
        dates() {
            const dates = this.selectedVet()?.calendar?.dates || {};

            return Object.entries(dates).map(([date, day]) => ({ date, label: day.label }));
        },
        slots() {
            return this.selectedVet()?.calendar?.dates?.[this.date]?.slots || [];
        },
        selectVet(id) {
            this.vetId = String(id);
            this.time = '';
            this.date = this.selectedVet()?.calendar?.earliest?.date || '';
        },
        chooseEarliest() {
            const earliest = this.selectedVet()?.calendar?.earliest;
            if (!earliest) {
                return;
            }
            this.date = earliest.date;
            this.time = earliest.value;
            this.step = 3;
        },
        next() {
            if (this.step === 1 && this.petId) {
                this.step = 2;
                return;
            }
            if (this.step === 2 && this.vetId && this.date) {
                this.step = 3;
            }
        },
        back() {
            if (this.step > 1) {
                this.step -= 1;
            }
        },
    }));

    Alpine.data('slotEditor', () => ({
        rows: [],
        init() {
            const source = document.getElementById('slot-editor-seed');
            const seed = source ? JSON.parse(source.textContent) : [];
            this.rows = Array.isArray(seed) ? seed.map((row) => ({
                weekday: String(row.weekday),
                start: row.start,
                end: row.end,
                slot_minutes: String(row.slot_minutes),
            })) : [];
        },
        add() {
            this.rows.push({ weekday: '1', start: '09:00', end: '12:00', slot_minutes: '30' });
        },
        remove(index) {
            this.rows.splice(index, 1);
        },
    }));

    Alpine.data('visitReschedule', (appointmentId) => ({
        date: '',
        time: '',
        calendar: { dates: {}, earliest: null },
        init() {
            const source = document.getElementById('vet-open-slots');
            const all = source ? JSON.parse(source.textContent) : {};
            this.calendar = all[String(appointmentId)] || { dates: {}, earliest: null };
            this.date = this.calendar.earliest?.date || '';
        },
        dateList() {
            return Object.entries(this.calendar.dates || {}).map(([date, day]) => ({
                date,
                label: day.label,
            }));
        },
        slots() {
            return this.calendar.dates?.[this.date]?.slots || [];
        },
    }));

    Alpine.data('symptomCheck', () => ({
        step: 1,
        species: '',
        symptoms: [],
        latitude: '',
        longitude: '',
        locationNote: '',
        speciesError: '',
        symptomError: '',
        submitting: false,
        init() {
            const checked = this.$el.querySelector('input[name="species"]:checked');
            const boxes = [...this.$el.querySelectorAll('input[name="symptoms[]"]:checked')];

            if (checked) {
                this.species = checked.value;
                this.step = 2;
            }

            this.symptoms = boxes.map((box) => box.value);
            this.askLocation();
        },
        next() {
            if (this.species === '') {
                this.speciesError = 'Choose the kind of animal.';
                return;
            }

            this.speciesError = '';
            this.step = 2;
        },
        onSubmit(event) {
            if (this.step === 1) {
                event.preventDefault();
                this.next();
                return;
            }

            if (this.symptoms.length === 0) {
                event.preventDefault();
                this.symptomError = 'Choose at least one symptom.';
                return;
            }

            this.submitting = true;
        },
        askLocation() {
            if (!navigator.geolocation) {
                this.locationNote = 'This browser cannot share a location. Clinics are listed without distance.';
                return;
            }

            navigator.geolocation.getCurrentPosition((position) => {
                this.latitude = Number(position.coords.latitude).toFixed(6);
                this.longitude = Number(position.coords.longitude).toFixed(6);
                this.locationNote = 'If this is an emergency, clinics will be sorted from your location.';
            }, () => {
                this.locationNote = 'Location is unavailable. You can still see clinics and search by city.';
            }, { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 });
        },
    }));

    Alpine.store('palette', {
        open: false,
        show() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        hide() {
            this.open = false;
            if (!Alpine.store('cart')?.open) {
                document.body.classList.remove('overflow-hidden');
            }
        },
    });

    Alpine.data('commandPalette', () => ({
        query: '',
        items: [],
        recent: [],
        active: 0,
        loading: false,
        error: '',
        shortcut: 'Ctrl+K',
        endpoint: '',
        panel: null,
        timer: null,
        controller: null,
        init() {
            const platform = navigator.userAgent || '';
            this.shortcut = /Mac|iPhone|iPad/.test(platform) ? '⌘K' : 'Ctrl+K';
            this.endpoint = this.$el.dataset.searchUrl;
            this.panel = this.$el.querySelector('#command-palette');
            this.recent = this.readRecent();
            this.$watch('$store.palette.open', (open) => {
                if (!open) {
                    return;
                }

                this.query = '';
                this.items = [];
                this.error = '';
                this.active = 0;
                this.recent = this.readRecent();
                this.search();
                this.$nextTick(() => this.$refs.input?.focus());
            });
        },
        rows() {
            if (this.query.trim() !== '') {
                return this.items;
            }

            const recent = this.recent.map((item) => ({ ...item, group: 'Recent' }));

            return [...recent, ...this.items];
        },
        onInput() {
            window.clearTimeout(this.timer);
            this.timer = window.setTimeout(() => this.search(), 200);
        },
        async search() {
            this.controller?.abort();
            const controller = new AbortController();
            this.controller = controller;
            this.loading = true;
            this.error = '';

            try {
                const url = new URL(this.endpoint, window.location.origin);
                const term = this.query.trim();

                if (term !== '') {
                    url.searchParams.set('q', term);
                }

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error('search');
                }

                const data = await response.json();
                this.items = Array.isArray(data.items) ? data.items : [];
                this.active = 0;
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                this.items = [];
                this.error = 'Search is unavailable. Try again.';
            } finally {
                if (this.controller === controller) {
                    this.loading = false;
                }
            }
        },
        onKeydown(event) {
            const meta = event.metaKey || event.ctrlKey;

            if (meta && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                if (this.$store.palette.open) {
                    this.close();
                } else {
                    this.$store.palette.show();
                }
                return;
            }

            if (!this.$store.palette.open) {
                return;
            }

            const rows = this.rows();

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.active = rows.length ? (this.active + 1) % rows.length : 0;
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.active = rows.length ? (this.active - 1 + rows.length) % rows.length : 0;
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (rows[this.active]) {
                    this.go(rows[this.active]);
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                this.close();
            }
        },
        go(row) {
            this.remember(row);
            this.close();
            window.location.assign(row.url);
        },
        close() {
            this.$store.palette.hide();
        },
        readRecent() {
            try {
                const parsed = JSON.parse(window.localStorage.getItem('fs-palette-recent') || '[]');

                return Array.isArray(parsed) ? parsed.slice(0, 5) : [];
            } catch {
                return [];
            }
        },
        remember(row) {
            const next = [{ id: row.id, label: row.label, hint: row.hint, url: row.url }, ...this.readRecent().filter((item) => item.url !== row.url)].slice(0, 5);
            window.localStorage.setItem('fs-palette-recent', JSON.stringify(next));
            this.recent = next;
        },
        trap(event) {
            const panel = this.panel;
            if (!panel || event.key !== 'Tab') {
                return;
            }

            const items = [...panel.querySelectorAll(focusableSelector)].filter((element) => element.getClientRects().length > 0);
            if (items.length === 0) {
                event.preventDefault();
                panel.focus();
                return;
            }

            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('fsAlerts', () => ({
        open: false,
        count: 0,
        latestId: 0,
        items: [],
        loading: false,
        loaded: false,
        error: '',
        timer: null,
        countUrl: '',
        listUrl: '',
        readUrl: '',
        onVisibility: null,
        init() {
            this.count = Number(this.$el.dataset.unread || 0);
            this.latestId = Number(this.$el.dataset.latestId || 0);
            this.countUrl = this.$el.dataset.countUrl;
            this.listUrl = this.$el.dataset.listUrl;
            this.readUrl = this.$el.dataset.readUrl;
            this.onVisibility = () => {
                if (document.hidden) {
                    window.clearInterval(this.timer);
                    this.timer = null;
                    return;
                }

                this.poll();
                this.schedule();
            };
            document.addEventListener('visibilitychange', this.onVisibility);
            this.schedule();
        },
        destroy() {
            window.clearInterval(this.timer);
            document.removeEventListener('visibilitychange', this.onVisibility);
        },
        schedule() {
            window.clearInterval(this.timer);
            if (document.hidden) {
                return;
            }

            this.timer = window.setInterval(() => this.poll(), 30000);
        },
        async toggle() {
            this.open = !this.open;
            if (this.open) {
                await this.load();
            }
        },
        async poll() {
            if (document.hidden) {
                return;
            }

            try {
                const response = await fetch(this.countUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('count');
                }

                const data = await response.json();
                const nextId = Number(data.latest_id || 0);
                const nextCount = Number(data.count || 0);

                if (nextId > this.latestId) {
                    await this.announce();
                }

                this.count = nextCount;
                this.latestId = Math.max(this.latestId, nextId);

                if (this.open) {
                    await this.load();
                }
            } catch {
                this.error = 'Could not refresh alerts.';
            }
        },
        async announce() {
            try {
                const response = await fetch(this.listUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('list');
                }

                const data = await response.json();
                const fresh = (data.items || []).filter((item) => Number(item.id) > this.latestId && !item.read);

                if (fresh.length === 0) {
                    window.dispatchEvent(new CustomEvent('fs-toast', {
                        detail: { message: 'You have a new notification.', variant: 'success' },
                    }));
                } else {
                    fresh.slice().reverse().forEach((item) => {
                        window.dispatchEvent(new CustomEvent('fs-toast', {
                            detail: { message: item.title, variant: 'success' },
                        }));
                    });
                }

                if (this.open) {
                    this.items = data.items || [];
                    this.loaded = true;
                }
            } catch {
                window.dispatchEvent(new CustomEvent('fs-toast', {
                    detail: { message: 'You have a new notification.', variant: 'success' },
                }));
            }
        },
        async load() {
            this.loading = true;
            this.error = '';

            try {
                const response = await fetch(this.listUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('list');
                }

                const data = await response.json();
                this.items = data.items || [];
                this.count = Number(data.count ?? this.count);
                this.loaded = true;
            } catch {
                this.error = 'Could not load notifications.';
            } finally {
                this.loading = false;
            }
        },
        async markRead(item) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            try {
                const response = await fetch(`${this.readUrl}/${item.id}/read`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('read');
                }

                const data = await response.json();
                item.read = true;
                this.count = Number(data.count ?? Math.max(0, this.count - 1));
            } catch {
                this.error = 'Could not mark that alert as read.';
            }
        },
    }));

    Alpine.store('cart', {
        open: false,
        busy: false,
        error: '',
        count: 0,
        total_label: '',
        currency: '',
        checkout_url: '',
        cart_url: '',
        shop_url: '',
        items: [],
        queue: Promise.resolve(),
        token: 0,
        init() {
            const boot = document.getElementById('fs-cart-boot');

            if (boot) {
                try {
                    this.apply(JSON.parse(boot.textContent || '{}'));
                } catch {
                    this.items = [];
                }
            }

            window.addEventListener('fs-cart-undo', (event) => {
                const detail = event.detail || {};

                if (detail.add_url) {
                    this.add(detail, detail.quantity || 1);
                }
            });
        },
        show() {
            this.open = true;
            this.error = '';
            document.body.classList.add('overflow-hidden');
            window.setTimeout(() => {
                document.querySelector('[data-cart-panel]')?.focus();
            }, 0);
        },
        hide() {
            this.open = false;

            if (!Alpine.store('palette')?.open) {
                document.body.classList.remove('overflow-hidden');
            }
        },
        apply(cart) {
            this.count = Number(cart.count ?? this.items.length);
            this.total_label = cart.total_label || this.total_label;
            this.currency = cart.currency || this.currency;
            this.checkout_url = cart.checkout_url || this.checkout_url;
            this.cart_url = cart.cart_url || this.cart_url;
            this.shop_url = cart.shop_url || this.shop_url;
            this.items = (cart.items || []).map((item) => ({ ...item, pending: false }));
            this.paintBadges();
        },
        paintBadges() {
            document.querySelectorAll('[data-cart-badge]').forEach((node) => {
                node.textContent = String(this.count);
                node.classList.toggle('hidden', this.count < 1);
            });
            document.querySelectorAll('[data-cart-phrase]').forEach((node) => {
                node.textContent = this.count > 0 ? `, ${this.count} in cart` : '';
            });
        },
        enqueue(work) {
            const run = this.queue.then(work, work);
            this.queue = run.catch(() => {});

            return run;
        },
        add(product, quantity = 1) {
            const productId = product.id || product.product_id;
            const qty = Math.max(1, Number(quantity) || 1);
            const cap = Math.min(20, Number(product.stock ?? 20));

            if (!product.add_url || qty > cap) {
                window.dispatchEvent(new CustomEvent('fs-toast', {
                    detail: {
                        message: cap < 1 ? `${product.name} is out of stock.` : `Only ${cap} left of ${product.name}.`,
                        variant: 'warning',
                    },
                }));

                return;
            }

            const existing = this.items.find((item) => item.product_id === productId);

            if (existing && existing.quantity + qty > cap) {
                window.dispatchEvent(new CustomEvent('fs-toast', {
                    detail: {
                        message: `Only ${cap} left of ${product.name}. Your cart already has ${existing.quantity}.`,
                        variant: 'warning',
                    },
                }));

                return;
            }

            const snapshot = this.snapshotLocal();

            if (existing) {
                existing.quantity += qty;
                existing.line_label = this.lineLabel(existing);
                existing.pending = true;
            } else {
                this.items.push({
                    id: `pending-${productId}`,
                    product_id: productId,
                    name: product.name,
                    quantity: qty,
                    stock: Number(product.stock ?? cap),
                    price_each: Number(product.price_each || product.price || 0),
                    price_label: product.price_label || '',
                    line_label: this.lineLabel({
                        price_each: Number(product.price_each || product.price || 0),
                        quantity: qty,
                        price_label: product.price_label,
                    }),
                    image: product.image || '',
                    url: product.url || this.shop_url,
                    add_url: product.add_url,
                    update_url: null,
                    remove_url: null,
                    pending: true,
                });
            }

            this.count = this.items.length;
            this.refreshTotal();
            this.error = '';
            this.show();
            window.dispatchEvent(new CustomEvent('fs-toast', {
                detail: { message: `${product.name} added to your cart.`, variant: 'success' },
            }));

            return this.enqueue(() => this.send(product.add_url, 'POST', { quantity: qty }, snapshot));
        },
        setQuantity(item, quantity) {
            const next = Number(quantity);

            if (next < 1) {
                this.remove(item);

                return;
            }

            const cap = Math.min(20, Number(item.stock || 20));

            if (next > cap) {
                window.dispatchEvent(new CustomEvent('fs-toast', {
                    detail: { message: `Only ${cap} left of ${item.name}.`, variant: 'warning' },
                }));

                return;
            }

            const snapshot = this.snapshotLocal();
            const productId = item.product_id;
            item.quantity = next;
            item.pending = true;
            item.line_label = this.lineLabel(item);
            this.refreshTotal();

            return this.enqueue(async () => {
                const current = this.items.find((row) => row.product_id === productId);
                const url = current?.update_url || item.update_url;

                if (!url) {
                    return;
                }

                await this.send(url, 'PUT', { quantity: next }, snapshot);
            });
        },
        remove(item) {
            const snapshot = this.snapshotLocal();
            const payload = { ...item };
            const productId = item.product_id;
            const removeUrl = item.remove_url;
            this.items = this.items.filter((row) => row.product_id !== productId);
            this.refreshTotal();
            window.dispatchEvent(new CustomEvent('fs-toast', {
                detail: {
                    message: `${item.name} removed from your cart.`,
                    variant: 'success',
                    action: 'Undo',
                    event: 'fs-cart-undo',
                    detail: payload,
                },
            }));

            return this.enqueue(async () => {
                let url = removeUrl;

                if (!url) {
                    url = this.items.find((row) => row.product_id === productId)?.remove_url;
                }

                if (!url) {
                    return;
                }

                await this.send(url, 'DELETE', {}, snapshot);
            });
        },
        checkout() {
            return this.enqueue(async () => {
                this.busy = true;
                this.error = '';

                try {
                    const response = await this.request(this.checkout_url, 'POST', {});
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw data;
                    }

                    window.location.assign(data.redirect || this.cart_url);
                } catch (error) {
                    this.error = this.messageFrom(error, 'Could not place that order.');
                    this.busy = false;
                    window.dispatchEvent(new CustomEvent('fs-toast', {
                        detail: { message: this.error, variant: 'danger' },
                    }));
                }
            });
        },
        refreshTotal() {
            const amount = this.items.reduce((sum, item) => sum + (Number(item.price_each) * Number(item.quantity)), 0);
            this.total_label = `${this.currency || ''}${amount.toFixed(2)}`;
            this.count = this.items.length;
            this.paintBadges();
        },
        snapshotLocal() {
            return {
                count: this.count,
                total_label: this.total_label,
                items: this.items.map((item) => ({ ...item })),
            };
        },
        lineLabel(item) {
            if (item.quantity === 1 && item.price_label) {
                return item.price_label;
            }

            const amount = (Number(item.price_each) * Number(item.quantity)).toFixed(2);

            return `${this.currency || ''}${amount}`;
        },
        async send(url, method, body, snapshot) {
            if (!url) {
                return;
            }

            const token = this.token + 1;
            this.token = token;
            this.busy = true;

            try {
                const response = await this.request(url, method, body);
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw data;
                }

                if (token === this.token && data.cart) {
                    this.apply(data.cart);
                    this.error = '';
                }
            } catch (error) {
                if (token === this.token) {
                    this.count = snapshot.count;
                    this.total_label = snapshot.total_label;
                    this.items = snapshot.items;
                    this.paintBadges();
                    this.error = this.messageFrom(error, 'Could not update your cart.');
                    window.dispatchEvent(new CustomEvent('fs-toast', {
                        detail: { message: this.error, variant: 'danger' },
                    }));
                }
            } finally {
                if (token === this.token) {
                    this.busy = false;
                }
            }
        },
        async request(url, method, body) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            return fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            });
        },
        messageFrom(error, fallback) {
            const lines = Object.values(error?.errors || {}).flat().filter(Boolean);

            return lines.join(' ') || error?.message || fallback;
        },
    });

    Alpine.data('fsCart', () => ({
        trap(event) {
            if (event.key !== 'Tab') {
                return;
            }

            const panel = this.$root.querySelector('[data-cart-panel]');
            const items = [...(panel?.querySelectorAll(focusableSelector) || [])].filter((element) => element.getClientRects().length > 0 && !element.disabled);

            if (items.length === 0) {
                event.preventDefault();
                panel?.focus();

                return;
            }

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    Alpine.data('fsShop', () => ({
        catalog: {},
        filtering: false,
        quick: null,
        quickError: '',
        loadingQuick: false,
        init() {
            const node = document.getElementById(this.$el?.dataset?.catalogId || 'shop-catalog');

            try {
                this.catalog = JSON.parse(node?.textContent || '{}');
            } catch {
                this.catalog = {};
            }
        },
        openQuick(id) {
            const product = this.catalog[String(id)] || null;
            this.loadingQuick = true;
            this.quickError = '';
            this.quick = product;
            this.$nextTick(() => {
                this.loadingQuick = false;

                if (!product) {
                    this.quick = null;
                    this.quickError = 'Could not open that product.';
                }

                this.$refs.quickDialog?.focus();
            });
        },
        closeQuick() {
            this.quick = null;
            this.quickError = '';
            this.loadingQuick = false;
        },
        addProduct(id) {
            const product = this.catalog[String(id)];

            if (!product?.add_url) {
                return;
            }

            Alpine.store('cart').add(product, 1);
        },
        onEscape() {
            if (Alpine.store('palette')?.open || Alpine.store('cart')?.open) {
                return;
            }

            if (this.quick || this.quickError || this.loadingQuick) {
                this.closeQuick();
            }
        },
        trapQuick(event) {
            if (event.key !== 'Tab') {
                return;
            }

            const panel = this.$refs.quickDialog;
            const items = [...(panel?.querySelectorAll(focusableSelector) || [])].filter((element) => element.getClientRects().length > 0 && !element.disabled);

            if (items.length === 0) {
                event.preventDefault();
                panel?.focus();

                return;
            }

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    registerAdoptDeck(Alpine);
}

export function hydrateIcons(root = document) {
    if (!root.querySelector('i[data-lucide], span[data-lucide]')) {
        return;
    }

    createIcons({
        icons,
        root,
        inTemplates: true,
        attrs: {
            'stroke-width': 1.75,
        },
    });
}

export function startIcons() {
    const run = () => {
        hydrateIcons();

        if (import.meta.env.DEV) {
            document.querySelectorAll('[data-ui-card] [data-ui-card]').forEach((card) => {
                console.warn('FurShield cards do not nest.', card);
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
        run();
    }

    let queued = false;
    const observer = new MutationObserver(() => {
        if (queued) {
            return;
        }

        queued = true;
        queueMicrotask(() => {
            queued = false;
            hydrateIcons();
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });
}
