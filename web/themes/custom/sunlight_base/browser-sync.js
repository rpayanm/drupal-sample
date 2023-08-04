/**
 * Require Browsersync
 */
const browserSync = require('browser-sync');
const options = require("./config");

/**
 * Run Browsersync with server config
 */

const host = options.config.host;

browserSync({
  proxy: host,
  host: host,
  open: 'external',
  files: [
    'public/**/*.js',
    'public/**/*.css'
  ],
});