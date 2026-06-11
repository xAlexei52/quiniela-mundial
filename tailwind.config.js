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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Anton', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                pitch: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                    950: '#052e16',
                },
                grass: '#0b6e3b',
                chalk: '#f8fafc',
                gold: '#fbbf24',
            },
            backgroundImage: {
                'pitch-stripes':
                    'repeating-linear-gradient(90deg, #0b6e3b, #0b6e3b 60px, #0a663708 60px, #0a663708 120px)',
            },
        },
    },

    plugins: [forms],
};
