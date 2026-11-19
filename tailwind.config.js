import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                surface: {
                    DEFAULT: 'rgb(var(--fs-surface) / <alpha-value>)',
                    raised: 'rgb(var(--fs-surface-raised) / <alpha-value>)',
                    sunken: 'rgb(var(--fs-surface-sunken) / <alpha-value>)',
                },
                text: {
                    DEFAULT: 'rgb(var(--fs-text) / <alpha-value>)',
                    muted: 'rgb(var(--fs-text-muted) / <alpha-value>)',
                    inverse: 'rgb(var(--fs-text-inverse) / <alpha-value>)',
                },
                border: {
                    DEFAULT: 'rgb(var(--fs-border) / <alpha-value>)',
                    strong: 'rgb(var(--fs-border-strong) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--fs-success) / <alpha-value>)',
                    soft: 'rgb(var(--fs-success-soft) / <alpha-value>)',
                },
                warning: {
                    DEFAULT: 'rgb(var(--fs-warning) / <alpha-value>)',
                    soft: 'rgb(var(--fs-warning-soft) / <alpha-value>)',
                },
                danger: {
                    DEFAULT: 'rgb(var(--fs-danger) / <alpha-value>)',
                    soft: 'rgb(var(--fs-danger-soft) / <alpha-value>)',
                },
                focus: 'rgb(var(--fs-focus) / <alpha-value>)',
                ink: {
                    DEFAULT: 'rgb(var(--fs-ink) / <alpha-value>)',
                    soft: 'rgb(var(--fs-ink-soft) / <alpha-value>)',
                    mute: 'rgb(var(--fs-ink-mute) / <alpha-value>)',
                },
                mist: {
                    DEFAULT: 'rgb(var(--fs-mist) / <alpha-value>)',
                    deep: 'rgb(var(--fs-mist-deep) / <alpha-value>)',
                },
                tide: {
                    DEFAULT: 'rgb(var(--fs-tide-rgb) / <alpha-value>)',
                    bright: 'rgb(var(--fs-tide-bright-rgb) / <alpha-value>)',
                    deep: 'rgb(var(--fs-tide-deep-rgb) / <alpha-value>)',
                },
                bloom: {
                    DEFAULT: '#E8A87C',
                    soft: '#F6D7C0',
                },
            },
            fontFamily: {
                display: ['Syne', ...defaultTheme.fontFamily.sans],
                hero: ['Montserrat', ...defaultTheme.fontFamily.sans],
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            spacing: {
                'fs-3xs': 'var(--fs-space-3xs)',
                'fs-2xs': 'var(--fs-space-2xs)',
                'fs-xs': 'var(--fs-space-xs)',
                'fs-sm': 'var(--fs-space-sm)',
                'fs-md': 'var(--fs-space-md)',
                'fs-lg': 'var(--fs-space-lg)',
                'fs-xl': 'var(--fs-space-xl)',
                'fs-2xl': 'var(--fs-space-2xl)',
                'fs-3xl': 'var(--fs-space-3xl)',
            },
            borderRadius: {
                'fs-xs': 'var(--fs-radius-xs)',
                'fs-sm': 'var(--fs-radius-sm)',
                'fs-md': 'var(--fs-radius-md)',
                'fs-lg': 'var(--fs-radius-lg)',
                'fs-xl': 'var(--fs-radius-xl)',
            },
            boxShadow: {
                glow: 'var(--fs-shadow-glow)',
                soft: 'var(--fs-shadow-soft)',
                'fs-xs': 'var(--fs-shadow-xs)',
                'fs-sm': 'var(--fs-shadow-sm)',
                'fs-md': 'var(--fs-shadow-md)',
                'fs-lg': 'var(--fs-shadow-lg)',
            },
            transitionDuration: {
                'fs-fast': 'var(--fs-duration-fast)',
                'fs-base': 'var(--fs-duration-base)',
                'fs-slow': 'var(--fs-duration-slow)',
            },
            transitionTimingFunction: {
                'fs-standard': 'var(--fs-ease-standard)',
                'fs-enter': 'var(--fs-ease-enter)',
                'fs-exit': 'var(--fs-ease-exit)',
            },
            keyframes: {
                floaty: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-12px)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '0% 50%' },
                    '100%': { backgroundPosition: '100% 50%' },
                },
            },
            animation: {
                floaty: 'floaty 6s ease-in-out infinite',
                shimmer: 'shimmer 8s ease infinite',
            },
            backgroundImage: {
                'hero-mesh':
                    'radial-gradient(circle at 15% 20%, rgba(25,168,155,0.28), transparent 40%), radial-gradient(circle at 85% 10%, rgba(232,168,124,0.25), transparent 35%), radial-gradient(circle at 70% 80%, rgba(14,124,116,0.18), transparent 45%), linear-gradient(160deg, #07181C 0%, #0E3A38 48%, #123C36 100%)',
                'page-wash': 'var(--fs-page-wash)',
            },
        },
    },

    plugins: [forms],
};
