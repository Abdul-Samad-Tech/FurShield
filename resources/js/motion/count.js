import { animate, inView } from 'motion';
import { enter } from './timing';

export function startCounters() {
    inView('[data-count]', (element) => {
        const target = Number(element.dataset.count);

        if (!Number.isFinite(target)) {
            return;
        }

        const suffix = element.dataset.countSuffix || '';
        const state = { value: 0 };

        animate(state, { value: target }, {
            ...enter,
            onUpdate: () => {
                element.textContent = `${Math.round(state.value)}${suffix}`;
            },
        });
    });
}
