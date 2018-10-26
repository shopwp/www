/*

Initializing

*/

import gulp from "gulp";
import config from "../config";

gulp.task('default', done => {
  gulp.series( gulp.parallel('js', 'css'), 'server', 'watch' )(done);
});
