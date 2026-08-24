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
                sans: ['"Space Grotesk"', 'ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                mobile: {
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
                brand: {
                    50: '#F1F8EA',
                    100: '#E0F0D0',
                    200: '#C2E1A1',
                    300: '#9BD06B',
                    400: '#7FC94A',
                    500: '#6FB63A',
                    600: '#4A8524',
                    700: '#3F711F',
                    800: '#2F5C15',
                    900: '#26461A',
                    950: '#14270A',
                    mark: '#6FB63A',
                    DEFAULT: '#4A8524',
                    hover: '#3F711F',
                    deep: '#2F5C15',
                    tint: '#F1F8EA',
                },
                surface: {
                    paper: '#FAF8F5',
                    linen: '#EDE9E2',
                    line: '#CFC8BC',
                    muted: '#888377',
                    ink: '#211E1A',
                    dark: '#1A1815',
                    elevated: '#2B2823',
                    50: '#FAF8F5',
                    100: '#F5F2EC',
                    200: '#EDE9E2',
                    300: '#DED8CE',
                    400: '#CFC8BC',
                    500: '#888377',
                    600: '#6B6257',
                    700: '#2B2823',
                    800: '#1A1815',
                    900: '#000',
                    950: '#000',
                },
                text: {
                    primary: '#211E1A',
                    secondary: '#888377',
                    muted: '#888377',
                    inverse: '#FAF8F5',
                    dark: '#F5F2EC',
                    'dark-muted': '#8F887C',
                },
                amber: {
                    DEFAULT: '#E4A03C',
                },
                status: {
                    success: '#4A8524',
                    warning: '#C8860E',
                    error: '#C2432C',
                    info: '#35708F',
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
                    'linear-gradient(135deg, #F1F8EA 0%, #E0F0D0 35%, #C2E1A1 100%)',
            },
        },
    },

    plugins: [forms],
};
