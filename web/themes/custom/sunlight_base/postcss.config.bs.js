module.exports = {
  plugins: {
    autoprefixer: {},
    'postcss-parent-selector': {selector: '.bootstrap', ignoredSelectors: ['body', ':root', '.modal-backdrop', '.modal-backdrop.show', '.modal-backdrop.fade'], ignoredSelectorsStartsWith: ['html']},
  }
}