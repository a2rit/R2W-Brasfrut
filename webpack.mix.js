let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 mix.js('resources/assets/js/app.js', 'public/js/app.js')
 .js('resources/assets/js/bottom-app.js', 'public/js/bottom-app.js')
 .sass('resources/assets/sass/app.scss', 'public/css');

if (mix.inProduction()) {
    mix.version();
    //mix.setResourceRoot('/producao/');
} else {
    mix.webpackConfig({
        devtool: 'source-map'
    }).sourceMaps()
}