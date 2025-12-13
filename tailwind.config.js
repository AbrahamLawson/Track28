/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            screens: {
                'xs': '475px',
                // sm: 640px (default)
                // md: 768px (default)
                // lg: 1024px (default)
                // xl: 1280px (default)
                // 2xl: 1536px (default)
            },
        },
    },
    plugins: [],
}
