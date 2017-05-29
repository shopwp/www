const browserSync = require('browser-sync').create();
const webpack = require('webpack');
const webpackDevMiddleware = require('webpack-dev-middleware');
const webpackHotMiddleware = require('webpack-hot-middleware');
const htmlInjector = require('bs-html-injector');
const webpackConfig = require('./webpack.config.babel');
const bundler = webpack(webpackConfig());
const PROXY_TARGET = 'wpshop.dev';

browserSync.use(htmlInjector, {
  restrictions: ['.wrap']
});

browserSync.init({
  files: [{
    // scss|js managed by webpack
    // optionally exclude other managed assets: images, fonts, etc
    match: [ 'src/**/*.!(scss|js|svg|jpg|png)' ],
    fn: synchronize,
  }],

  proxy: {
    target: PROXY_TARGET,

    middleware: [
      webpackDevMiddleware(bundler, {
        publicPath: webpackConfig().output.publicPath,
        noInfo: true,
      }),
      webpackHotMiddleware(bundler),
    ]
  }
});

function synchronize(event, file) {
  if( file.endsWith('php') ) {
    htmlInjector();
  }
}
