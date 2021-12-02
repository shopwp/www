import webpack from 'webpack';
import gulp from 'gulp';
import config from '../config';
import webpackStream from 'webpack-stream';

gulp.task('js', (done) => {
  return gulp
    .src(config.files.jsEntry)
    .pipe(webpackStream(config.webpackConfig(), webpack))
    .pipe(gulp.dest(config.folders.dist));
});
