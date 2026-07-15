import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                'nbbeu-navy': '#16305C',
                'nbbeu-navy-deep': '#0B1D3A',
                'nbbeu-teal': '#18AFBF',
                'nbbeu-gold': '#B08D3D',
                'nbbeu-bg': '#F6F7F9',
                'nbbeu-ink': '#232A33',
            },
            fontFamily: {
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Spectral', 'serif'],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [forms],
};
