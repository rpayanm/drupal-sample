let mix = require('laravel-mix');

mix.webpackConfig({
  externals: {
    'wp': 'wp',
    'react': 'React',
    'react-dom': 'ReactDOM',
  }
});

mix
    .js('./blocks/js/index.js', 'dist')
    .react()
    .js('./blocks/js/drupal/sl-bs-accordion.js', 'dist')
    .sass('./blocks/sass/style.scss', 'dist')
    .sass('./blocks/sass/editor.scss', 'dist');