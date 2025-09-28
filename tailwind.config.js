module.exports = {
  content: ['./templates/**/*.php', './src/js/**/*.js'],
  theme: {
    extend: {
      keyframes: {
        traceSquare: {
          '0%, 100%': { transform: 'translate(0, 0)' },
          '25%': { transform: 'translate(3rem, 0)' },
          '50%': { transform: 'translate(3rem, 3rem)' },
          '75%': { transform: 'translate(0, 3rem)' },
        },
      },
      animation: {
        'trace-square': 'traceSquare 2s linear infinite',
      },
    },
  },
  plugins: [],
  safelist: [
    // Tambahkan pola kalau ada kelas Tailwind yang dibentuk dinamis di PHP/ACF
    // { pattern: /(bg|text|border)-(red|blue|green|yellow)-(100|200|500|700)/ },
  ],
};
