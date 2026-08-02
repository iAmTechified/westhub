/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#EAFAFF',
                    100: '#14ABD5',
                    200: '#075EA6',
                    300: '#1C3F78',
                },
                admin: {
                    surface: '#07111f',
                    ink: '#EAF5FF',
                    muted: '#97ACBE',
                    stroke: 'rgba(255,255,255,0.14)',
                },
                state: {
                    success: '#22C55E',
                    warn: '#F59E0B',
                    error: '#F43F5E',
                },
            },
            fontFamily: {
                sans: ['"Funnel Display"', 'sans-serif'],
                display: ['"Funnel Display"', 'sans-serif'],
            },
            transitionDuration: {
                fast: '140ms',
                base: '180ms',
                slow: '280ms',
            },
            boxShadow: {
                glass: '0 16px 48px rgba(7, 94, 166, 0.2)',
            },
            backdropBlur: {
                glass: '16px',
                soft: '8px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
