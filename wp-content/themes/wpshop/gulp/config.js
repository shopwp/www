import webpack from 'webpack';
import colormin from 'postcss-colormin';
import cssnano from 'cssnano';
import autoprefixer from 'autoprefixer';
import presetEnv from 'postcss-preset-env';
import ProgressBarPlugin from 'progress-bar-webpack-plugin';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import BrowserSyncPlugin from 'browser-sync-webpack-plugin';
import browserSync from 'browser-sync';

var config = {
  files: {
    js: ['./assets/js/app/**/*.js', './assets/js/app/**/*.jsx'],
    css: './assets/css/app/**/*.scss',
    cssEntry: './assets/css/app/app.scss',
  },
  folders: {
    plugin: './',
    dist: './assets/prod',
    cache: './node_modules/.cache',
  },
  names: {
    css: 'app.min.css',
    js: 'app.min.js',
  },
  serverName: 'wpshopify-web.loc',
  isBuilding: true,
  bs: browserSync.create(),
};

/*

Postcss Config

*/
function postCSSPlugins() {
  return [
    autoprefixer(),
    presetEnv(),
    colormin({
      legacy: true,
    }),
    cssnano(),
  ];
}

/*

Style Lint Config

*/
function stylelintConfig() {
  return {
    config: {
      rules: {
        'declaration-block-no-duplicate-properties': true,
        'block-no-empty': true,
        'no-extra-semicolons': true,
        'font-family-no-duplicate-names': true,
      },
    },
    debug: true,
    reporters: [{ formatter: 'string', console: true }],
  };
}

config.postCSSPlugins = postCSSPlugins;
config.stylelintConfig = stylelintConfig;

export default config;
