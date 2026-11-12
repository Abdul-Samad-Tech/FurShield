import { animate, hover } from 'motion';
import { follow, settle } from './timing';
import { clamp, trackPointer } from './pointer';

const limit = 8;

export function startMagnetic() {
    hover('[data-magnetic]', (element) => {
        const previous = element.style.transitionProperty;
        element.style.transitionProperty = 'color, background-color, border-color, box-shadow, opacity';

        const stop = trackPointer(element, (event) => {
            const rect = element.getBoundingClientRect();
            const x = clamp((event.clientX - rect.left - rect.width / 2) * 0.25, -limit, limit);
            const y = clamp((event.clientY - rect.top - rect.height / 2) * 0.25, -limit, limit);
            animate(element, { transform: `translate(${x}px, ${y}px)` }, follow);
        });

        return () => {
            stop();
            animate(element, { transform: 'translate(0px, 0px)' }, settle).finished.then(() => {
                element.style.transform = '';
                element.style.transitionProperty = previous;
            });
        };
    });
}
