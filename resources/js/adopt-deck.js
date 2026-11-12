import { animate } from 'motion';

const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

let flight = null;

function stopFlight() {
    flight?.stop();
    flight = null;
}

function haptic() {
    try {
        if (typeof navigator.vibrate === 'function') {
            navigator.vibrate(12);
        }
    } catch {
        // vibrate can exist and still be blocked.
    }
}

export function registerAdoptDeck(Alpine) {
    Alpine.data('fsAdopt', () => ({
        listings: [],
        remaining: [],
        history: [],
        view: 'deck',
        reduced: false,
        canInterest: false,
        guest: false,
        loginUrl: '',
        sheet: '',
        sheetCard: null,
        sheetError: '',
        sending: false,
        deckError: '',
        live: '',
        flying: false,
        pointerId: null,
        dragging: false,
        originX: 0,
        originY: 0,
        lastX: 0,
        lastY: 0,
        lastTime: 0,
        velocityX: 0,
        velocityY: 0,
        init() {
            this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.canInterest = this.$el.dataset.canInterest === '1';
            this.guest = this.$el.dataset.guest === '1';
            this.loginUrl = this.$el.dataset.loginUrl || '';
            this.view = window.localStorage.getItem('fs-adopt-view') === 'grid' ? 'grid' : 'deck';

            try {
                const parsed = JSON.parse(document.getElementById('adopt-deck')?.textContent || '[]');
                this.listings = Array.isArray(parsed) ? parsed : [];
            } catch {
                this.listings = [];
                this.deckError = 'The adoption deck could not be loaded.';
            }

            this.remaining = this.listings.slice();
        },
        setView(view) {
            this.view = view;
            window.localStorage.setItem('fs-adopt-view', view);
        },
        stack() {
            return this.remaining.slice(0, 3);
        },
        onKeydown(event) {
            if (this.view !== 'deck' || this.sheet) {
                return;
            }

            if (event.target.closest('input, textarea, select')) {
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.act('left');
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.act('right');
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.act('up');
            } else if ((event.key === 'z' || event.key === 'Z') && (event.metaKey || event.ctrlKey)) {
                event.preventDefault();
                this.undo();
            }
        },
        onPointerDown(event) {
            if (this.reduced || this.flying || this.sheet || event.button !== 0) {
                return;
            }

            if (event.target.closest('button, a, input, textarea, select, label')) {
                return;
            }

            stopFlight();
            const card = event.currentTarget;
            this.dragging = true;
            this.pointerId = event.pointerId;
            this.originX = event.clientX;
            this.originY = event.clientY;
            this.lastX = event.clientX;
            this.lastY = event.clientY;
            this.lastTime = event.timeStamp;
            this.velocityX = 0;
            this.velocityY = 0;
            card.style.willChange = 'transform';
            card.setPointerCapture(event.pointerId);
        },
        onPointerMove(event) {
            if (!this.dragging || event.pointerId !== this.pointerId) {
                return;
            }

            const dx = event.clientX - this.originX;
            const dy = event.clientY - this.originY;
            const dt = event.timeStamp - this.lastTime;

            if (dt > 0) {
                this.velocityX = (event.clientX - this.lastX) / dt;
                this.velocityY = (event.clientY - this.lastY) / dt;
            }

            this.lastX = event.clientX;
            this.lastY = event.clientY;
            this.lastTime = event.timeStamp;
            this.place(event.currentTarget, dx, dy);

            if (event.cancelable) {
                event.preventDefault();
            }
        },
        onPointerUp(event) {
            if (!this.dragging || event.pointerId !== this.pointerId) {
                return;
            }

            const card = event.currentTarget;
            const dx = event.clientX - this.originX;
            const dy = event.clientY - this.originY;
            this.dragging = false;
            this.pointerId = null;
            card.style.willChange = '';
            const direction = this.directionFrom(dx, dy);

            if (!direction) {
                this.snap(card);
                return;
            }

            this.fly(card, direction);
        },
        directionFrom(dx, dy) {
            const absX = Math.abs(dx);
            const absY = Math.abs(dy);
            const flickX = Math.abs(this.velocityX) > 0.65;
            const flickY = Math.abs(this.velocityY) > 0.65;

            if (dy < 0 && absY > absX && (absY > 88 || flickY)) {
                return 'up';
            }

            if (dx > 0 && (absX > 96 || flickX) && absX > absY) {
                return 'right';
            }

            if (dx < 0 && (absX > 96 || flickX) && absX > absY) {
                return 'left';
            }

            return '';
        },
        place(card, dx, dy) {
            const rotate = Math.max(-8, Math.min(8, dx / 28));
            card.style.transform = `translate3d(${dx}px, ${dy}px, 0) rotate(${rotate}deg)`;
            const right = card.querySelector('[data-stamp="right"]');
            const left = card.querySelector('[data-stamp="left"]');
            const up = card.querySelector('[data-stamp="up"]');

            if (right) {
                right.style.opacity = String(Math.max(0, Math.min(1, dx / 120)));
            }

            if (left) {
                left.style.opacity = String(Math.max(0, Math.min(1, -dx / 120)));
            }

            if (up) {
                up.style.opacity = String(Math.max(0, Math.min(1, -dy / 120)));
            }
        },
        snap(card) {
            this.clearStamps(card);
            const run = animate(card, { transform: 'none' }, { duration: 0.3, ease: [0.2, 0, 0, 1] });
            flight = run;
            run.finished.finally(() => {
                if (flight === run) {
                    card.style.transform = '';
                    flight = null;
                }
            });
        },
        act(direction) {
            if (this.flying || this.remaining.length === 0) {
                return;
            }

            const card = this.$root.querySelector('[data-deck-top="true"]');

            if (!card || this.reduced) {
                this.commit(direction, card);
                return;
            }

            this.fly(card, direction);
        },
        fly(card, direction) {
            stopFlight();
            this.flying = true;
            const from = card.style.transform || 'none';
            const x = direction === 'right' ? 520 : direction === 'left' ? -520 : 0;
            const y = direction === 'up' ? -620 : 36;
            const rotate = direction === 'right' ? 8 : direction === 'left' ? -8 : 0;
            const run = animate(card, {
                transform: [from, `translate3d(${x}px, ${y}px, 0) rotate(${rotate}deg)`],
                opacity: [1, 0],
            }, { duration: 0.32, ease: [0.4, 0, 1, 1] });
            flight = run;
            run.finished.then(() => {
                if (flight === run) {
                    this.commit(direction, card);
                }
            }).catch(() => {
                if (flight === run) {
                    this.flying = false;
                }
            });
        },
        commit(direction, card) {
            const current = this.remaining[0];

            if (!current) {
                this.flying = false;
                return;
            }

            if (card) {
                card.style.transform = '';
                card.style.opacity = '';
                this.clearStamps(card);
            }

            this.remaining = this.remaining.slice(1);
            this.history = this.history.concat([{ card: current, direction }]);
            this.flying = false;
            haptic();
            this.live = direction === 'left'
                ? `Skipped ${current.name}.`
                : direction === 'right'
                    ? `Interested in ${current.name}.`
                    : `Details for ${current.name}.`;

            if (direction === 'right') {
                this.openSheet('interest', current);
            } else if (direction === 'up') {
                this.openSheet('details', current);
            }
        },
        undo() {
            const last = this.history[this.history.length - 1];

            if (!last || this.flying) {
                return;
            }

            this.history = this.history.slice(0, -1);
            this.remaining = [last.card].concat(this.remaining);

            if (this.sheetCard && this.sheetCard.id === last.card.id) {
                this.closeSheet();
            }

            this.live = `${last.card.name} is back on the deck.`;
        },
        openSheet(mode, card) {
            this.sheet = mode;
            this.sheetCard = card;
            this.sheetError = '';
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => {
                const dialog = this.$refs.sheetDialog;
                dialog?.focus();
                if (mode === 'interest') {
                    dialog?.querySelector('textarea')?.focus();
                }
            });
        },
        closeSheet() {
            this.sheet = '';
            this.sheetCard = null;
            this.sheetError = '';
            const palette = Alpine.store('palette');
            const cart = Alpine.store('cart');

            if (!palette?.open && !cart?.open) {
                document.body.classList.remove('overflow-hidden');
            }
        },
        async submitInterest(event) {
            if (!this.sheetCard) {
                return;
            }

            this.sending = true;
            this.sheetError = '';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch(this.sheetCard.interest_url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(event.target),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw data;
                }

                window.dispatchEvent(new CustomEvent('fs-toast', {
                    detail: { message: data.message || 'Interest submitted to the shelter.', variant: 'success' },
                }));
                this.closeSheet();
            } catch (error) {
                const lines = Object.values(error?.errors || {}).flat().filter(Boolean);
                this.sheetError = lines.join(' ') || error?.message || 'Could not send that interest.';
            } finally {
                this.sending = false;
            }
        },
        trap(event) {
            if (event.key !== 'Tab') {
                return;
            }

            const panel = this.$refs.sheetDialog;
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
        clearStamps(card) {
            card.querySelectorAll('[data-stamp]').forEach((stamp) => {
                stamp.style.opacity = '0';
            });
        },
    }));
}
