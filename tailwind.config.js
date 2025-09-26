module.exports = {
  content: [
    "./templates/**/*.php",
    "./src/js/**/*.js",
  ],
  theme: { extend: {} },
  plugins: [],
  safelist: [
    // Tambahkan pola kalau ada kelas Tailwind yang dibentuk dinamis di PHP/ACF
    // { pattern: /(bg|text|border)-(red|blue|green|yellow)-(100|200|500|700)/ },
  ],
};
