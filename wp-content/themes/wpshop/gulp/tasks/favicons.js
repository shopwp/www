//////////////
// Favicons //
//////////////

import gulp from 'gulp';
import config from '../config';
import favicons from 'gulp-favicons';

gulp.task('favicons', function() {
  gulp.src(config.favicon.entry).pipe(favicons({
      appName: "Pet Haven",
      appDescription: "Pet Haven",
      developerName: "Pet Haven",
      developerURL: "http://www.pethavenmn.org",
      background: "#fff",
      path: config.favicon.all,
      url: "http://www.pethavenmn.org",
      display: "standalone",
      orientation: "portrait",
      version: 1.0,
      logging: false,
      online: false,
      html: "index.html",
      replace: true,
      icons: {
        android: true,
        appleIcon: true,
        appleStartup: true,
        coast: true,
        favicons: true,
        firefox: true,
        opengraph: true,
        twitter: true,
        windows: true,
        yandex: true
      }
  })).pipe(gulp.dest(config.favicon.dest));
});
