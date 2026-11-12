import './bootstrap';
import Alpine from 'alpinejs';
import { registerAssistant } from './assistant';
import { startMotion } from './motion';
import { registerUi, startIcons } from './ui';

window.Alpine = Alpine;

registerUi(Alpine);
registerAssistant(Alpine);
Alpine.start();
startIcons();
startMotion();

const nearby = document.querySelector('[data-nearby]');

if (nearby) {
    import('./maps/nearby.js').then(({ startNearby }) => startNearby(nearby));
}

const adminCharts = document.querySelector('[data-admin-charts]');

if (adminCharts) {
    import('./admin/charts.js')
        .then(({ startAdminCharts }) => startAdminCharts(adminCharts))
        .catch(() => {
            window.dispatchEvent(new CustomEvent('fs-chart', { detail: { id: '*', status: 'error' } }));
        });
}
