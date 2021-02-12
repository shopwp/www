/////////////////
// Watch files //
/////////////////

import gulp from 'gulp';
import config from '../config';

function reload(done) {
  config.bs.reload();
  done();
}

gulp.task('watch', (done) => {
  gulp.watch(config.files.css, gulp.series('css', reload));
});
