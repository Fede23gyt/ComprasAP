import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0055A4',    // Azul base
                    light: '#4D87C3',      // Para hover o fondos suaves
                    dark: '#003F7D',       // Para bordes, headers
                },
                accent: {
                    DEFAULT: '#00C38A',    // Verde acento
                    light: '#4FE1AF',      // Variante clara
                    dark: '#00996B',       // Variante oscura
                },
                background: {
                    DEFAULT: '#FFFFFF',
                    muted: '#F8FAFC',      // Gris muy claro para secciones
                },
                text: {
                    DEFAULT: '#333333',    // Texto principal
                    muted: '#666666',      // Texto secundario
                },
            },
        },
    },

    plugins: [forms],
};
