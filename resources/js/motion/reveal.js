import { animate, inView, stagger } from 'motion';
import { enter } from './timing';

const distance = '16px';

function frames(direction) {
    const from = {
        up: `translateY(${distance})`,
        down: `translateY(-${distance})`,
        left: `translateX(${distance})`,
        right: `translateX(-${distance})`,
        zoom: 'scale(0.96)',
    }[direction] || `translateY(${distance})`;

    return { opacity: [0, 1], transform: [from, 'none'] };
}

function finish(element) {
    element.dataset.revealed = 'true';
    element.style.opacity = '';
    element.style.transform = '';
}

export function startReveal() {
    const parents = new Set();

    document.querySelectorAll('[data-reveal]').forEach((element) => {
        if (element.parentElement) {
            parents.add(element.parentElement);
        }
    });

    parents.forEach((parent) => {
        const items = [...parent.children].filter((child) => child.hasAttribute('data-reveal'));

        if (items.length === 0) {
            return;
        }

        inView(items[0], () => {
            const groups = new Map();

            items.forEach((element) => {
                const direction = element.dataset.reveal || 'up';
                const group = groups.get(direction) || [];
                group.push(element);
                groups.set(direction, group);
            });

            groups.forEach((group, direction) => {
                const step = group.length > 1 ? Math.min(0.05, 0.2 / (group.length - 1)) : 0;

                animate(group, frames(direction), {
                    ...enter,
                    delay: stagger(step),
                }).finished.finally(() => {
                    group.forEach(finish);
                });
            });
        });
    });
}
