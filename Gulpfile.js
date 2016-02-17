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

var browserify = require('browserify');
var babelify = require('babelify');
var source = require('vinyl-source-stream');


var config = {
    sass: {
        includePaths: [
            'node_modules/govuk_frontend_toolkit/stylesheets',
            'node_modules/govuk-elements/public/sass'
        ]
    },
    jsSrc: 'src/AppBundle/Resources/assets/javascripts',
    imgSrc: 'src/AppBundle/Resources/assets/images',
    sassSrc: 'src/AppBundle/Resources/assets/scss',
    reactSrc: 'src/AppBundle/Resources/assets/react',
    webAssets: 'web/assets'
};

gulp.task('gettag', function(callback) {
    config.webAssets = "web/assets/" + new Date().getTime();
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

    gulp.src('./node_modules/govuk_template_mustache/assets/stylesheets/images/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/images'));

    gulp.src('./node_modules/govuk_template_mustache/assets/stylesheets/images/gov.uk_logotype_crown.png')
        .pipe(gulp.dest('./web/images'));

    gulp.src(config.sassSrc + '/images/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/images'));

    gulp.src('./node_modules/govuk-elements/public/images/**/*')
        .pipe(gulp.dest('./web/images'));

    callback();
});
gulp.task('sass.fonts', function() {
    gulp.src('node_modules/govuk_template_mustache/assets/stylesheets/fonts/*').pipe(gulp.dest(config.webAssets + '/stylesheets/fonts'));
    gulp.src('node_modules/govuk_template_mustache/assets/stylesheets/fonts-ie8.css').pipe(gulp.dest(config.webAssets + '/stylesheets'));

});

gulp.task('images', function () {
    gulp.src('./node_modules/govuk_frontend_toolkit/images/**/*').pipe(gulp.dest('./web/images'));
    gulp.src('./src/AppBundle/Resources/assets/images/**/*').pipe(gulp.dest('./web/images'));
});

gulp.task('js', function(callback) {
    runSequence('lint.js',['js.uglify','js.vendor','js.ie'], callback);
});
gulp.task('js.uglify', function () {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
            config.jsSrc + '/*.js'])
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.webAssets + '/javascripts'))
        .pipe(rename('application.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
});
gulp.task('js.ie', function() {
    gulp.src('./node_modules/govuk_template_mustache/assets/javascripts/ie.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
    gulp.src('./node_modules/govuk_template_mustache/assets/javascripts/vendor/goog/webfont-debug.js').pipe(gulp.dest(config.webAssets + '/javascripts'));
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
gulp.task('watch', function() {
    gulp.watch(config.sassSrc + '/**/*',{ interval: 1000 }, ['sass']);
    gulp.watch(config.imgSrc + '/**/*', { interval: 1000 }, ['images']);
    gulp.watch(config.jsSrc + '/**/*.js', { interval: 1000 }, ['js']);
    gulp.watch(config.jsSrc + '/**/*.jsx', { interval: 1000 }, ['react-debug']);
});

gulp.task('react-debug', function (callback) {

    browserify({
        entries: config.reactSrc + '/transfers.jsx',
        extensions: ['.jsx'],
        debug: true
    })
    .transform(babelify)
    .bundle()
    .pipe(source('transfers.js'))
    .pipe(gulp.dest('./web/javascripts/'));
    
});

gulp.task('react', function (callback) {
    
    function build() {
        browserify({
            entries: config.reactSrc + '/transfers.jsx',
            extensions: ['.jsx'],
            debug: false
        })
        .transform(babelify)
        .bundle()
        .pipe(source('transfers.js'))
        .pipe(gulp.dest('./web/javascripts/'));
        
    }
    
    function minify() {
        return gulp.src('./web/javascripts/transfers.js')
            .pipe(rename('transfers.min.js'))
            .pipe(uglify())
            .pipe(gulp.dest('./web/javascripts'));
    }
    
    build();
    minify();
});

gulp.task('default', function(callback) {
    runSequence( 'sass.application','gettag', 'clean', ['sass','images','js'], 'react', callback);
});
gulp.task('dev', function (callback) {
    runSequence('gettag', 'clean', ['sass','images','js'], 'watch', callback);
});
gulp.task('watchsass', function(callback) {
    gulp.watch(config.sassSrc + '/**/*', ['lint.sass','sass.application','sass.images','sass.fonts']);
});
