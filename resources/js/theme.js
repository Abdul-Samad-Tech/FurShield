const storageKey = 'furshield-theme';

function isDark() {
    return document.documentElement.classList.contains('dark');
}

function label(dark) {
    return dark ? 'Switch to light theme' : 'Switch to dark theme';
}

function apply(dark) {
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    localStorage.setItem(storageKey, dark ? 'dark' : 'light');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', label(dark));
        button.setAttribute('aria-pressed', dark ? 'true' : 'false');
    });

    document.documentElement.dispatchEvent(new CustomEvent('furshield-theme', { bubbles: true }));
}

export function startTheme() {
    apply(isDark());

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-theme-toggle]');

        if (!button) {
            return;
        }

        apply(!isDark());
    });
}
