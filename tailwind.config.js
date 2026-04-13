import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    // Ficha embarcação (painel lateral): garantir variantes lg mesmo se o scan falhar num build antigo/cache.
    safelist: ['lg:sticky', 'lg:top-6', 'lg:z-10', 'lg:self-stretch'],
    content: [
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    softer: '#d1fae5',
                    'softer-dark': '#022c22',
                },
                fg: {
                    'brand-strong': '#065f46',
                    'brand-strong-dark': '#d1fae5',
                },
            },
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
