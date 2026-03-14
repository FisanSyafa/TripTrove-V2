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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans], // Tambahkan Poppins
            },
            colors: { // Tambahkan warna kustom
                'brand-dark': '#0a0a0a',
                'brand-blue': '#007bff',
                'brand-cyan': '#00d4ff',
                'brand-light': '#b0b0b0', // Warna abu-abu terang untuk teks
                'brand-card': 'rgba(0, 123, 255, 0.1)', // Warna latar card
                'brand-border': 'rgba(0, 123, 255, 0.3)', // Warna border card
            },
        },
    },

    plugins: [
        forms,
        require('@tailwindcss/typography'),
    ],
};