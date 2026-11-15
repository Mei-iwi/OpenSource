import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const accent = {
    50: '#f0f6f4', 100: '#e1ede9', 200: '#c5dcd5', 300: '#a6c8bd',
    400: '#80aea2', 500: '#538a7e', 600: '#356b61', 700: '#28564e',
    800: '#24463f', 900: '#213a35', 950: '#152723',
};
const neutral = {
    50: '#f7f7f5', 100: '#efefec', 200: '#dfe2dd', 300: '#c7ccc5',
    400: '#a6aea5', 500: '#818b81', 600: '#626d64', 700: '#4a554d',
    800: '#333e38', 900: '#222c27', 950: '#171e1a',
};
const danger = {
    50: '#faf5f3', 100: '#f1e7e3', 200: '#dfcdc7', 300: '#c5a9a0',
    400: '#aa857b', 500: '#936d63', 600: '#79574f', 700: '#624740',
    800: '#4f3b36', 900: '#3d302c', 950: '#29211f',
};

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
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: accent,
                slate: neutral, gray: neutral,
                indigo: accent, blue: accent, sky: accent, cyan: accent,
                violet: accent, purple: accent, teal: accent,
                emerald: accent, green: accent, lime: accent,
                amber: neutral, yellow: neutral, orange: neutral,
                rose: danger, red: danger, pink: danger,
            },
        },
    },

    plugins: [forms],
};
