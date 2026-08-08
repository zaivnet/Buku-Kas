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
            },

            // Palet warna sesuai 03-DESIGN.md §2
            colors: {
                primary: {
                    DEFAULT: '#1E40AF',
                    50:  '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                },
                success: {
                    DEFAULT: '#16A34A',
                    50:  '#F0FDF4',
                    100: '#DCFCE7',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                },
                danger: {
                    DEFAULT: '#DC2626',
                    50:  '#FEF2F2',
                    100: '#FEE2E2',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                },
                warning: {
                    DEFAULT: '#D97706',
                    50:  '#FFFBEB',
                    100: '#FEF3C7',
                    500: '#F59E0B',
                    600: '#D97706',
                    700: '#B45309',
                },
                neutral: {
                    bg:      '#F8FAFC',
                    surface: '#FFFFFF',
                    border:  '#E2E8F0',
                    text:    '#0F172A',
                    muted:   '#64748B',
                },
            },
        },
    },

    plugins: [forms],
};
