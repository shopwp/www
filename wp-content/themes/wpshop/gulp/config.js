////////////
// Config //
////////////

import browserSync from 'browser-sync';

const config = {
  files: {
    js: [
      './assets/js/app/**/*.js',
      '!./assets/js/app.min.js',
      '!./assets/js/vendor.min.js',
      '!./assets/js/app.min.js.map'
    ],
    jsEntry: './assets/js/app/app.js',
    css: './assets/css/**/*.scss',
    cssEntry: './assets/css/app/app.scss'
  },
  favicon: {
    entry: './assets/imgs/favicons/favicon.png',
    dest: './assets/imgs/favicons',
    all: './assets/imgs/favicons/**/*'
  },
  folders: {
    css: './assets/css',
    js: './assets/js'
  },
  names: {
    jsVendor: 'vendor.min.js',
    js: 'app.min.js',
    css: 'app.min.css'
  },
  libs: [
    'jquery',
    'asynquence-contrib',
    'rx',
    'imagesloaded',
    'lodash'
  ],
  bs: browserSync.create(),
  serverName: "wpshop.dev"

};

export default config;
