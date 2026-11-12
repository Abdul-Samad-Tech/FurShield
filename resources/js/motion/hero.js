import { startHeroScene } from '../hero/boot';
import { animate, stagger } from 'motion';
import { enter } from './timing';

export function startHero() {
    const hero = document.querySelector('[data-hero]');

    if (!hero) {
        return;
    }

    const copy = [hero.querySelector('[data-hero-brand]'), hero.querySelector('[data-hero-copy]'), hero.querySelector('[data-hero-cta]')].filter(Boolean);

    copy.forEach((element) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(16px)';
    });

    if (copy.length > 0) {
        animate(copy, { opacity: [0, 1], transform: ['translateY(16px)', 'none'] }, {
            ...enter,
            delay: stagger(0.08),
        }).finished.finally(() => {
            copy.forEach((element) => {
                element.style.opacity = '';
                element.style.transform = '';
            });
        });
    }

    startHeroScene(hero);
}
