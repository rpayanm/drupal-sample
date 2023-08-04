const bs_vars = require('./bootstrap.json');
const bs_colors = bs_vars.theme_colors;

module.exports = {
  prefix: 'tw-',
  important: true,
  darkMode: 'media',
  content: [
    './*.theme',
    './assets/js/**/*.js',
    './templates/**/*.twig',
    './components/**/*.twig',
  ],
  safelist: [

  ],
  theme: {
    screens: {
      // Use it like this:
      // @screen sm {
      //   /* ... */
      // }
      // 576px and up
      // => @media (min-width: 576px) { ... }
      'sm': '576px',
      'md': '768px',
      'lg': '992px',
      'xl': '1200px',
      'xxl': '1400px',
    },
    extend: {
      colors: bs_colors,
    },
  },
  plugins: [ ],
  corePlugins: {
    preflight: false,
  }
}
