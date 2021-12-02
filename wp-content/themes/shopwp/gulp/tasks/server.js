////////////
// Server //
////////////

import gulp from 'gulp';
import config from '../config';

function reload(done) {
  config.bs.reload();
  done();
}

gulp.task('server', (done) => {
  config.bs.init({
    proxy: config.serverName,
    port: 5000,
    notify: false,
  });

  gulp.watch(config.files.css, gulp.series('css', reload));

  //done();
});
