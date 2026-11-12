import { scopeMotion } from 'motion';
import { startCounters } from './count';
import { startHero } from './hero';
import { startMagnetic } from './magnetic';
import { startReveal } from './reveal';
import { startTilt } from './tilt';

export function startMotion() {
    scopeMotion(() => {
        startHero();
        startReveal();
        startCounters();

        scopeMotion(() => {
            startTilt();
            startMagnetic();
        }, { matchMedia: 'hover' });
    }, { matchMedia: 'motion' });
}
