'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    del = require('del'),
    minifyCss = require('gulp-minify-css'),
    importCss = require('gulp-import-css'),
    runSequence = require('run-sequence'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
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
    },
    jsSrc: 'src/AppBundle/Resources/assets/javascripts',
    imgSrc: 'src/AppBundle/Resources/assets/images',
    sassSrc: 'src/AppBundle/Resources/assets/scss',
    webAssets: 'web/assets'
};

gulp.task('gettag', function(callback) {
    config.webAssets = "web/assets/" + new Date().getTime()
    callback();
});
gulp.task('clean', function (callback) {
    del(['web/assets']).then(function() {
        callback();
    });
});

gulp.task('sass', function(callback) {
    runSequence(
        'lint.sass',['sass.application', 'sass.application-ie7', 'sass.application-ie8', 'sass.application-print','sass.images','sass.fonts'],
        callback);
});
gulp.task('sass.application', function () {

    return gulp.src(config.sassSrc + '/application.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
    
});
gulp.task('sass.application-ie7', function () {

    return gulp.src(config.sassSrc + '/application-ie7.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.application-ie8', function () {

    return gulp.src(config.sassSrc + '/application-ie8.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.application-print', function () {

    return gulp.src(config.sassSrc + '/application-print.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.images', function(callback) {
    gulp.src('./node_modules/govuk-elements/govuk/public/stylesheets/images/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/images'));
    
    gulp.src(config.sassSrc + '/images/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/images'));
    callback();
});
gulp.task('sass.fonts', function() {
    gulp.src('./node_modules/govuk-elements/govuk/public/stylesheets/fonts/*').pipe(gulp.dest(config.webAssets + '/stylesheets/fonts'));
    gulp.src('./node_modules/govuk-elements/govuk/public/stylesheets/fonts-ie8.css').pipe(gulp.dest(config.webAssets + '/stylesheets'));
    
});

gulp.task('images', function () {
    gulp.src('./node_modules/govuk-elements/govuk/public/images/**/*').pipe(gulp.dest('./web/images'));
    gulp.src('./src/AppBundle/Resources/assets/images/**/*').pipe(gulp.dest('./web/images'));
});

gulp.task('js', function(callback) {
    runSequence('lint.js',['js.uglify','js.vendor','js.ie'], callback);
});
gulp.task('js.uglify', function () {
    return gulp.src([
            './node_modules/govuk-elements/govuk/public/javascripts/govuk-template.js',
            './node_modules/govuk-elements/govuk/public/javascripts/govuk/selection-buttons.js',
            './node_modules/moj-template/source/assets/javascripts/moj.js',
            config.jsSrc + '/*.js'])
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.webAssets + '/javascripts'))
        .pipe(rename('application.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});
gulp.task('js.ie', function() {
    gulp.src('./node_modules/govuk-elements/govuk/public/javascripts/ie.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
    gulp.src('./node_modules/govuk-elements/govuk/public/javascripts/vendor/goog/webfont-debug.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
});
gulp.task('js.vendor', function () {
    gulp.src('./node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

gulp.task('lint.sass', function() {
    return gulp.src('src/AppBundle/Resources/assets/scss/**/*.scss')
        .pipe(scsslint());
});
gulp.task('lint.js', function (callback) {
    gulp.src(config.jsSrc + '/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
    
    callback();
});

// Rerun the task when a file changes
gulp.task('watch', ['default'], function() {
    gulp.watch(config.sassSrc + '/**/*', ['sass']);
    gulp.watch(config.imgSrc + '/**/*', ['images']);
    gulp.watch(config.jsSrc + '/**/*', ['js']);
});

gulp.task('default', function(callback) {
    runSequence('gettag', 'clean', ['sass','images','js'], callback);
});
gulp.task('dev', function (callback) {
    runSequence('gettag', 'clean', ['sass','images','js'], 'watch', callback);
});

