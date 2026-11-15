import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const accent = {
    50: '#f3f3ff', 100: '#e9e9ff', 200: '#d3d3ff', 300: '#b5b4fb',
    400: '#9693ef', 500: '#7772df', 600: '#5b55cc', 700: '#4944ae',
    800: '#3c398a', 900: '#302f68', 950: '#202044',
};
const blueAccent = {
    50: '#f1f5ff', 100: '#e5edff', 200: '#cbdcff', 300: '#a5c3fb',
    400: '#79a3ed', 500: '#5c84dc', 600: '#4268c1', 700: '#36539c',
    800: '#30477d', 900: '#293b62', 950: '#1b2943',
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
                indigo: accent, violet: accent, purple: accent,
                blue: blueAccent, sky: blueAccent, cyan: blueAccent,
                teal: blueAccent,
                emerald: blueAccent, green: blueAccent, lime: blueAccent,
                amber: neutral, yellow: neutral, orange: neutral,
                rose: danger, red: danger, pink: danger,
            },
        },
    },

    plugins: [forms],
};
