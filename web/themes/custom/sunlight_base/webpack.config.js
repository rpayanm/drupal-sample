const Encore = require('@symfony/webpack-encore');
const jsonImporter = require('node-sass-json-importer');

// Manually configure the runtime environment if not already configured yet by
// the "encore" command. It's useful when you use tools that rely on
// webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // There is a weird caching issue with BrowserSync.
    // How to fix it (use only one):
    // 1 - Use a unique name in ".addEntry" section.
    // 2 - Change the two paths below to, for example, 'public/build/app/123' and
    // '.../public/build/app/123' and change it back to 'public/build/app' and
    // '.../public/build/app' respectively.

    // directory where compiled assets will be stored
    .setOutputPath('public/base')
    // public path used by the web server to access the output path
    .setPublicPath('/themes/custom/sunlight_base/public/base')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('tailwind', './assets/js/tailwind.js')
    .addEntry('base', './assets/js/base.js')
    .addEntry('gutenberg-edit', './assets/js/gutenberg-edit.js')
    .addEntry('gutenberg-view', './assets/js/gutenberg-view.js')

    // Sunlight
    // .addEntry('bootstrap', './assets/js/sunlight/swiper.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    // .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for
    // greater optimization. .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    // .enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
      config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
      config.useBuiltIns = 'usage';
      config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader((options) => {
      options.sassOptions = {
        importer: jsonImporter(),
      };
    }, {})
    .enablePostCssLoader((options) => {
      options.postcssOptions = {
        // directory where the postcss.config.js file is stored
        config: './postcss.config.base.js'
      };
    })

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
// .enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
// .autoProvidejQuery()
;

const base = Encore.getWebpackConfig();
base.name = 'base';

Encore.reset();

// Manually configure the runtime environment if not already configured yet by
// the "encore" command. It's useful when you use tools that rely on
// webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/bootstrap')
    // public path used by the web server to access the output path
    .setPublicPath('/themes/custom/sunlight_base/public/bootstrap')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('bootstrap', './assets/js/bootstrap.js')
    .addEntry('bootstrap-icons', './assets/js/bootstrap-icons.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    // .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for
    // greater optimization. .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    // .enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
      config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
      config.useBuiltIns = 'usage';
      config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader((options) => {
      options.sassOptions = {
        importer: jsonImporter(),
      };
    }, {})
    .enablePostCssLoader((options) => {
      options.postcssOptions = {
        // directory where the postcss.config.js file is stored
        config: './postcss.config.bs.js'
      };
    })

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
// .enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
// .autoProvidejQuery()
;

//module.exports = Encore.getWebpackConfig();

const bootstrap = Encore.getWebpackConfig();
bootstrap.name = 'bootstrap';

module.exports = [base, bootstrap];