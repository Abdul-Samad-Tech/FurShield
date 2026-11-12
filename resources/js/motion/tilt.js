import { animate, hover } from 'motion';
import { follow, settle } from './timing';
import { trackPointer } from './pointer';

const maxTilt = 4;

export function startTilt() {
    hover('[data-tilt]', (element) => {
        const previous = element.style.transitionProperty;
        element.style.transitionProperty = 'color, background-color, border-color, box-shadow, opacity';

        const stop = trackPointer(element, (event) => {
            const rect = element.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width - 0.5;
            const py = (event.clientY - rect.top) / rect.height - 0.5;

            const rotateX = py * -maxTilt;
            const rotateY = px * maxTilt;
            animate(element, {
                transform: `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`,
            }, follow);
        });

        return () => {
            stop();
            animate(element, { transform: 'perspective(900px) rotateX(0deg) rotateY(0deg)' }, settle).finished.then(() => {
                element.style.transform = '';
                element.style.transitionProperty = previous;
            });
        };
    });
}
