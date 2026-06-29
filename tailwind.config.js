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
            pattern: /bg-brand-(50|100|200|300|400|500|600|700|800|900)/,
            variants: ['hover', 'focus', 'active'],
        },
        {
            pattern: /text-brand-(50|100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern: /border-brand-(50|100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern: /shadow-brand-(50|100|200|300|400|500|600|700|800|900)/,
        },
        // Store admin dashboard colors
        {
            pattern: /bg-(emerald|amber|violet|teal)-(50|100|200|300|400|500|600|700|800|900)/,
            variants: ['hover'],
        },
        {
            pattern: /text-(emerald|amber|violet|teal)-(50|100|200|300|400|500|600|700|800|900)/,
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
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e',
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
                surface: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },
            },
            boxShadow: {
                'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                'premium': '0 10px 30px -10px rgba(0, 0, 0, 0.1)',
                'card': '0 4px 24px rgba(0, 0, 0, 0.06)',
                'hover': '0 20px 40px -15px rgba(0, 0, 0, 0.15)',
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
                'hero-pattern': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            },
        },
    },

    plugins: [forms],
};
