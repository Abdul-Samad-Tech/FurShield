const smallScreen = '(max-width: 767px)';

function webglAvailable() {
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl2') || canvas.getContext('webgl');

        return Boolean(gl);
    } catch {
        return false;
    }
}

export function canMountHeroScene() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return false;
    }

    if (window.matchMedia(smallScreen).matches) {
        return false;
    }

    const cores = navigator.hardwareConcurrency;

    if (typeof cores === 'number' && cores <= 4) {
        return false;
    }

    return webglAvailable();
}

export function startHeroScene(hero) {
    if (!hero || !canMountHeroScene()) {
        return;
    }

    const launch = () => {
        import('./scene.js')
            .then(({ mountHeroScene }) => {
                if (!canMountHeroScene()) {
                    return;
                }

                mountHeroScene(hero);
            })
            .catch(() => {});
    };

    requestAnimationFrame(() => requestAnimationFrame(launch));
}
