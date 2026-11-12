export function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

export function trackPointer(element, onFrame) {
    let pending = false;
    let event = null;

    const onMove = (next) => {
        event = next;

        if (pending) {
            return;
        }

        pending = true;
        requestAnimationFrame(() => {
            pending = false;

            if (event) {
                onFrame(event);
            }
        });
    };

    element.addEventListener('pointermove', onMove);

    return () => element.removeEventListener('pointermove', onMove);
}
