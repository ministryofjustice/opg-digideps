'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    clean = require('gulp-clean'),
    minifyCss = require('gulp-minify-css'),
    sourcemaps = require('gulp-sourcemaps'),
    importCss = require('gulp-import-css'),
    runSequence = require('run-sequence'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    gutil = require('gulp-util'),
    rename = require('gulp-rename'),
    scsslint = require('gulp-scss-lint'),
    jshint = require('gulp-jshint');

var config = {
    sass: {
        includePaths: [
            'node_modules/govuk-elements/govuk/public/sass',
            'node_modules/govuk-elements/govuk/public/sass/design-patterns',
            'node_modules/govuk-elements/public/sass',
            'node_modules/govuk-elements/public/sass/elements/',
            'node_modules/govuk-elements/public/sass/elements/forms',
            'node_modules/moj-template/source/assets/stylesheets'
        ]
    }
};

gulp.task('sass', function(callback) {
    runSequence(
        'sass.clean',
        ['sass.application', 'sass.application-ie7', 'sass.application-ie8', 'sass.application-print'],
        'sass.images',
        callback);
});
gulp.task('sass.clean', function (callback) {
    gulp.src('./web/stylesheets/*', {read: false})
        .pipe(clean());
    callback();
});
gulp.task('sass.application', function () {

    return gulp.src('src/AppBundle/Resources/assets/scss/application.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./web/stylesheets'))
        .on('error', gutil.log);
    
});
gulp.task('sass.application-ie7', function () {

    return gulp.src('src/AppBundle/Resources/assets/scss/application-ie7.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./web/stylesheets'));

});
gulp.task('sass.application-ie8', function () {

    return gulp.src('src/AppBundle/Resources/assets/scss/application-ie8.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./web/stylesheets'));

});
gulp.task('sass.application-print', function () {

    return gulp.src('src/AppBundle/Resources/assets/scss/application-print.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('./web/stylesheets'));

});
gulp.task('sass.images', function(callback) {
    gulp.src('./node_modules/govuk-elements/govuk/public/stylesheets/images/**/*')
        .pipe(gulp.dest('./web/stylesheets/images'));
    callback();
});

gulp.task('images', function (callback) {
    runSequence('images.clean','images.copy', callback);
});
gulp.task('images.clean', function (callback) {
    gulp.src('./web/images/*', {read: false}).pipe(clean());
    callback();
});
gulp.task('images.copy', function (){
    gulp.src('./node_modules/govuk-elements/govuk/public/images/**/*').pipe(gulp.dest('./web/images'));
    gulp.src('./src/AppBundle/Resources/assets/images/**/*').pipe(gulp.dest('./web/images')); 
});

gulp.task('js', function(callback) {
    runSequence('js.clean',['js.uglify','js.vendor'], callback);
});
gulp.task('js.clean', function(callback) {
    gulp.src('./web/javascripts/*', {read: false})
        .pipe(clean());
    callback();   
});
gulp.task('js.uglify', function () {

    return gulp.src([
            './node_modules/govuk-elements/govuk/public/javascripts/govuk-template.js',
            './node_modules/govuk-elements/govuk/public/javascripts/govuk/selection-buttons.js',
            './node_modules/moj-template/source/assets/javascripts/moj.js',
            './src/AppBundle/Resources/assets/javascripts/*.js'])
        .pipe(concat('application.js'))
        .pipe(gulp.dest('./web/javascripts'))
        .pipe(rename('application.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./web/javascripts'));
});
gulp.task('js.vendor', function () {
    gulp.src('./node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest('./web/javascripts'));
});
gulp.task('lint',['lint.sass','lint.js']);
gulp.task('lint.sass', function() {
    return gulp.src('src/AppBundle/Resources/assets/scss/**/*.scss')
        .pipe(scsslint());
});
gulp.task('lint.js', function () {
    return gulp.src('src/AppBundle/Resources/assets/javascripts/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default')) 
});

gulp.task('clean', function(callback) {
    runSequence('js.clean','sass.clean','images.clean', callback); 
});

gulp.task('default', function(callback) {
    runSequence('clean','sass','images','js', callback);
});
