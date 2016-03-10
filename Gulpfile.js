'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    del = require('del'),
    minifyCss = require('gulp-minify-css'),
    importCss = require('gulp-import-css'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    scsslint = require('gulp-scss-lint'),
    jshint = require('gulp-jshint');

var browserify = require('browserify');
var babelify = require('babelify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');

var now = new Date().getTime();

var config = {
    sass: {
        includePaths: [
            'node_modules/govuk_frontend_toolkit/stylesheets',
            'node_modules/govuk-elements-sass/public/sass'
        ]
    },
    jsSrc: 'src/AppBundle/Resources/assets/javascripts',
    imgSrc: 'src/AppBundle/Resources/assets/images',
    sassSrc: 'src/AppBundle/Resources/assets/scss',
    reactSrc: 'src/AppBundle/Resources/assets/react',
    webAssets: 'web/assets/' + now
};

gulp.task('clean', () => {
    del(['web/assets']);
});

gulp.task('sass', [
    'lint.sass',
    'sass.application',
    'sass.application-ie7',
    'sass.application-ie8',
    'sass.application-print',
    'sass.images',
    'sass.fonts']);

gulp.task('sass.application', () => {

    return gulp.src(config.sassSrc + '/application.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.application-ie7', () => {

    return gulp.src(config.sassSrc + '/application-ie7.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.application-ie8', () => {

    return gulp.src(config.sassSrc + '/application-ie8.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.application-print', () => {

    return gulp.src(config.sassSrc + '/application-print.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(minifyCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});
gulp.task('sass.images', () => {
    gulp.src('./node_modules/govuk_template_mustache/assets/stylesheets/images/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/images'));

    gulp.src('./node_modules/govuk_template_mustache/assets/stylesheets/images/gov.uk_logotype_crown.png')
        .pipe(gulp.dest('./web/images'));

    gulp.src(config.sassSrc + '/images/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/images'));
});
gulp.task('sass.fonts', () => {
    gulp.src('node_modules/govuk_template_mustache/assets/stylesheets/fonts/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/fonts'));

    gulp.src('node_modules/govuk_template_mustache/assets/stylesheets/fonts-ie8.css')
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
});

gulp.task('images', () => {
    gulp.src('./node_modules/govuk_frontend_toolkit/images/**/*').pipe(gulp.dest('./web/images'));
    gulp.src('./src/AppBundle/Resources/assets/images/**/*').pipe(gulp.dest('./web/images'));
});

gulp.task('js.prod', ['lint.js'], () => {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
            config.jsSrc + '/*.js'])
        .pipe(concat('application.js'))
        .pipe(uglify())
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});
gulp.task('js.debug', function () {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
            config.jsSrc + '/*.js'])
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});
gulp.task('js.ie', function() {
    gulp.src('./node_modules/govuk_template_mustache/assets/javascripts/ie.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
    gulp.src('./node_modules/govuk_template_mustache/assets/javascripts/vendor/goog/webfont-debug.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
});
gulp.task('vendor', function () {
    gulp.src('./node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

gulp.task('lint.sass', function() {
    return gulp.src('src/AppBundle/Resources/assets/scss/**/*.scss')
        .pipe(scsslint());
});
gulp.task('lint.js', function () {
    gulp.src(config.jsSrc + '/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

// Rerun the task when a file changes
gulp.task('watch', ['clean', 'lint.js', 'sass', 'images', 'js.debug', 'js.ie', 'vendor', 'react-debug'], () => {
    gulp.watch(config.sassSrc + '/**/*', { interval: 1000 }, ['sass']);
    gulp.watch(config.imgSrc + '/**/*', { interval: 1000 }, ['images']);
    gulp.watch(config.jsSrc + '/**/*.js', { interval: 1000 }, ['lint.js', 'js.debug']);
    gulp.watch(config.reactSrc + '/**/*.jsx', { interval: 1000 }, ['react-debug']);
});

gulp.task('react-debug', () => {
    browserify({
        entries: config.reactSrc + '/transfers.jsx',
        extensions: ['.jsx'],
        debug: true
    })
    .transform(babelify)
    .bundle()
    .pipe(source('transfers.js'))
    .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

gulp.task('react', () => {
    browserify({
        entries: config.reactSrc + '/transfers.jsx',
        extensions: ['.jsx'],
        debug: false
    })
    .transform(babelify)
    .bundle()
    .pipe(source('transfers.js'))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

gulp.task('default', ['clean', 'lint.js', 'sass', 'images', 'js.prod', 'js.ie', 'vendor', 'react']);
gulp.task('dev', ['watch']);
