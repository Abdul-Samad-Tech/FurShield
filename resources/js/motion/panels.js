import { animate } from 'motion';

export function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function clear(panel) {
    panel.style.opacity = '';
    panel.style.transform = '';
}

export function leavePanel(panel) {
    if (!panel || prefersReducedMotion()) {
        return Promise.resolve();
    }

    return animate(panel, { opacity: 0, transform: 'translateY(8px)' }, { duration: 0.2, ease: 'easeOut' }).finished.finally(() => clear(panel));
}

export function enterPanel(panel) {
    if (!panel || prefersReducedMotion()) {
        return;
    }

    panel.style.opacity = '0';
    panel.style.transform = 'translateY(8px)';

    animate(panel, { opacity: [0, 1], transform: ['translateY(8px)', 'none'] }, { duration: 0.24, ease: 'easeOut' }).finished.then(() => clear(panel));
}
