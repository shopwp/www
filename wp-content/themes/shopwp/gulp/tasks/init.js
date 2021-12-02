import gulp from 'gulp';

gulp.task('default', (done) => {
  gulp.series('css', 'server')(done);
});
