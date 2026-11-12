/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
    boxShadow: {
        neon: '0 0 30px rgba(99,102,241,.35)',
    },
    animation: {
        float: 'float 6s ease-in-out infinite',
    },
    keyframes: {
        float: {
            '0%,100%': { transform: 'translateY(0)' },
            '50%': { transform: 'translateY(-8px)' }
        }
}
    },
  },
  plugins: [],
}