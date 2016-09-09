'use strict';

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    del = require('del'),
    uglifycss = require('gulp-uglifycss'),
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
    webAssets: 'web/assets/' + now
};

// Remove previous builds of javascript and css files
gulp.task('clean', () => {
    del(['web/assets']);
});

// Build all style related files for all browsers and copy
// related images and font files along with them
// DEBUG MODE: No minification
gulp.task('sass.debug', [
    'sass.debug.application',
    'sass.application-ie7',
    'sass.application-ie8',
    'sass.application-print',
    'sass.images',
    'sass.fonts']);

// Build all style related files for all browsers and copy
// related images and font files along with them
// PRODUCTION: Lint and minification
gulp.task('sass.prod', [
    'lint.sass',
    'sass.application',
    'sass.application-ie7',
    'sass.application-ie8',
    'sass.application-print',
    'sass.images',
    'sass.fonts']);

// Compile the sass for the main styles for the site into a .css file
// Sourcemaps created as well
gulp.task('sass.debug.application', () => {

    return gulp.src(config.sassSrc + '/application.scss')
        .pipe(sourcemaps.init())
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});

// Compile the sass for the main styles for the site into a minified .css file
gulp.task('sass.application', () => {

    return gulp.src(config.sassSrc + '/application.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(uglifycss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});

// Compile styles that are unique to IE 7.
gulp.task('sass.application-ie7', () => {

    return gulp.src(config.sassSrc + '/application-ie7.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(uglifycss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});

// Compile styles that are unique to IE 8
gulp.task('sass.application-ie8', () => {

    return gulp.src(config.sassSrc + '/application-ie8.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});

// Compile styles that are used when the user prints something.
gulp.task('sass.application-print', () => {

    return gulp.src(config.sassSrc + '/application-print.scss')
        .pipe(sass(config.sass))
        .pipe(importCss())
        .pipe(uglifycss())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));

});

// Copy all style related images, we also bundle the external copy of fonts too, only used for ie 8
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

// Copy non css related images
gulp.task('images', () => {
    gulp.src('./node_modules/govuk_frontend_toolkit/images/**/*').pipe(gulp.dest('./web/images'));
    gulp.src('./node_modules/govuk_template_mustache/assets/images/*').pipe(gulp.dest('./web/images'));
    gulp.src('./src/AppBundle/Resources/assets/images/**/*').pipe(gulp.dest('./web/images'));
});


// Creates the production version of the service javascript.
// Files are concatinated and then minified with uglify.
gulp.task('js.prod', ['lint.js'], () => {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
            config.jsSrc + '/*.js'])
        .pipe(concat('application.js'))
        .pipe(uglify())
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

// create a debug version of javascript to allow easier debugging by
// having javascript that can easily have breakpoints and stepped through
// Used by the watch process.
gulp.task('js.debug', function () {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
            config.jsSrc + '/*.js'])
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

// Create IE javascript with polyfills for missing functions and support.
gulp.task('js.ie', function() {
    gulp.src('./node_modules/govuk_template_mustache/assets/javascripts/ie.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
    gulp.src('./node_modules/govuk_template_mustache/assets/javascripts/vendor/goog/webfont-debug.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
});

// Copy across javascript from other vendors.
gulp.task('vendor', function () {
    gulp.src('./node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});

// Check that the sass complies with simple rules for its creation to encourage good code.
gulp.task('lint.sass', function() {
    return gulp.src('src/AppBundle/Resources/assets/scss/**/*.scss')
        .pipe(scsslint());
});

// Check javascript follows some good guidelines and check for obvious errors.
gulp.task('lint.js', function () {
    gulp.src(config.jsSrc + '/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});
// Watch the source files and recompile in debug mode when there are changed.
gulp.task('watch', ['clean', 'lint.js', 'sass.debug', 'images', 'js.debug', 'js.ie', 'vendor'], () => {
    gulp.watch(config.sassSrc + '/**/*', { interval: 1000 }, ['sass.debug']);
    gulp.watch(config.sassSrc + '/*', { interval: 1000 }, ['sass.debug']);
    gulp.watch(config.imgSrc + '/**/*', { interval: 1000 }, ['images']);
    gulp.watch(config.jsSrc + '/**/*.js', { interval: 1000 }, ['lint.js', 'js.debug']);
});

// Build all assets in production ready mode.
gulp.task('default', ['clean', 'lint.js', 'sass.prod', 'images', 'js.prod', 'js.ie', 'vendor']);
