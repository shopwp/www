///////////////
// JS Vendor //
///////////////

import gulp from 'gulp';
import config from '../config';
import browserify from 'browserify';
import babelify from 'babelify';
import uglify from 'gulp-uglify';
import source from 'vinyl-source-stream';
import buffer from 'vinyl-buffer';
import rename from "gulp-rename";

gulp.task('js-vendor', () => {

  const b = browserify({
    debug: true
  });

  // require all libs specified in libs array
  config.libs.forEach(lib => {
    b.require(lib);
  });

  return b.bundle()
    .pipe(source(config.names.jsVendor))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(rename(config.names.jsVendor))
    .pipe(gulp.dest(config.folders.js));
});
