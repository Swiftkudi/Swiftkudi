const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    // Safelist dynamic classes used in Blade templates
    safelist: [
        // Dynamic background colors
        { pattern: /^bg-(indigo|purple|pink|green|emerald|blue|cyan|orange|amber|yellow|red|rose|violet|teal)-\d+00?$/ },
        { pattern: /^bg-gradient-to-(r|l|t|b|tr|tl|br|bl)$/ },
        // Dynamic text colors
        { pattern: /^text-(indigo|purple|pink|green|emerald|blue|cyan|orange|amber|yellow|red|rose|violet|teal|gray|white)-\d+00?$/ },
        // Dynamic border colors
        { pattern: /^border-(indigo|purple|pink|green|emerald|blue|cyan|orange|amber|yellow|red|rose|violet|teal|gray)-\d+00?$/ },
        // Dark mode variants
        'dark:bg-dark-800',
        'dark:bg-dark-900',
        'dark:bg-dark-950',
        'dark:bg-dark-700',
        'dark:text-gray-100',
        'dark:text-gray-300',
        'dark:text-gray-400',
        'dark:border-dark-600',
        'dark:border-dark-700',
        // Animation classes
        'animate-float',
        'animate-float-delayed',
        'animate-pulse-glow',
        // Custom classes
        'glass-card',
        'gradient-text',
        'gradient-text-alt',
    ],

    theme: {
        extend: {
            fontFamily: {
                heading: ['var(--font-heading-name)', 'Plus Jakarta Sans', 'sans-serif'],
                body: ['var(--font-body-name)', 'Inter', 'sans-serif'],
                sans: ['Inter', 'Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                dark: {
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
                }
            },
            animation: {
                'float': 'float 6s ease-in-out infinite',
                'float-delayed': 'float 6s ease-in-out infinite 2s',
                'pulse-glow': 'pulse-glow 3s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
                'pulse-glow': {
                    '0%, 100%': { boxShadow: '0 0 20px rgba(99, 102, 241, 0.3)' },
                    '50%': { boxShadow: '0 0 40px rgba(99, 102, 241, 0.6)' },
                },
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
