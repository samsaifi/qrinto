import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.jsx',
    ],

    safelist: [
        {
            pattern: /bg-brand-(50|100|200|300|400|500|600|700|800|900|950)/,
            variants: ['hover', 'focus', 'active'],
        },
        {
            pattern: /text-brand-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /border-brand-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /shadow-brand-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        // Store admin dashboard colors
        {
            pattern: /bg-(emerald|amber|violet|teal)-(50|100|200|300|400|500|600|700|800|900|950)/,
            variants: ['hover'],
        },
        {
            pattern: /text-(emerald|amber|violet|teal)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /border-(emerald|amber|violet|teal)-(100|200|500)/,
        },
        {
            pattern: /ring-(emerald|teal)-(200|500)/,
        },
        {
            pattern: /from-(emerald|teal)-(400|500|600|700)/,
        },
        {
            pattern: /via-teal-(600)/,
        },
        {
            pattern: /to-(emerald|teal)-(600|700)/,
        },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#F5FAF1',
                    100: '#EAF5DD',
                    200: '#D2EBB8',
                    300: '#B5DE89',
                    400: '#8BCF52',
                    500: '#6FBA3B',
                    600: '#5A9A2F',
                    700: '#487A25',
                    800: '#355A1C',
                    900: '#243D12',
                    950: '#14230A',
                },
                surface: {
                    50: '#FCFBF9',
                    100: '#F8F7F3',
                    200: '#F1EEE8',
                    300: '#E7E1D7',
                    400: '#D7CFC2',
                    500: '#B9B0A2',
                    600: '#92887A',
                    700: '#6B6257',
                    800: '#403A33',
                    900: '#24211D',
                    950: '#151311',
                },
                text: {
                    primary: '#24211D',
                    secondary: '#6B6257',
                    muted: '#92887A',
                    inverse: '#FFFFFF',
                },
                accent: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
            },
            boxShadow: {
                xs: '0 1px 2px rgba(0,0,0,.04)',
                sm: '0 2px 6px rgba(0,0,0,.05)',
                card: '0 8px 24px rgba(0,0,0,.06)',
                premium: '0 20px 50px rgba(0,0,0,.08)',
                hover: '0 30px 60px rgba(0,0,0,.12)',
                glass: '0 8px 32px rgba(0,0,0,.05)',
            },
            animation: {
                'float': 'float 6s ease-in-out infinite',
                'slide-up': 'slideUp 0.5s ease-out',
                'fade-in': 'fadeIn 0.5s ease-out',
                'pulse-soft': 'pulseSoft 2s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.8' },
                },
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'hero-pattern':
                    'linear-gradient(135deg, #F5FAF1 0%, #EAF5DD 35%, #D2EBB8 100%)',
            },
        },
    },

    plugins: [forms],
};
