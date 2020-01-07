var gulp = require('gulp');
var sass = require('gulp-sass');

var paths = {
    sass: {
        src: './assets/scss/**/*.scss',
        dest: './assets/css'
    }
};

// ---------------------------------------------- Gulp Tasks
gulp.task('sass', function () {
  return gulp.src(paths.sass.src)
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest(paths.sass.dest))
});

// ------------------------------------ Gulp Testing Message
gulp.task('message', function(){
  console.log('It works!!');
});

// ---------------------------------------------- Gulp Watch
gulp.task('watch:styles', function () {
  gulp.watch(paths.sass.src, gulp.series('sass'));
});

gulp.task('watch', gulp.series('sass',
  gulp.parallel('watch:styles')
));


// -------------------------------------------- Default task
gulp.task('default', gulp.series('sass',
  gulp.parallel('message', 'watch')
));
