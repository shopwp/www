/////////////
// Default //
/////////////

import gulp from 'gulp';

gulp.task('default',
  gulp.parallel('js-app', 'css', 'server')
);
