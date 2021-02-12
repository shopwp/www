import gulp from 'gulp';
import config from '../config';
import sass from 'gulp-sass';
import rename from 'gulp-rename';
import postcss from 'gulp-postcss';

gulp.task('css', () => {
  return gulp
    .src(config.files.cssEntry)
    .pipe(sass())
    .pipe(postcss(config.postCSSPlugins()))
    .pipe(rename(config.names.css))
    .pipe(gulp.dest(config.folders.dist))
    .pipe(config.bs.stream());
});
