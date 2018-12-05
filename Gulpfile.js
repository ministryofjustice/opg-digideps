'use strict';

var gulp = require('gulp'),
    gutil = require('gulp-util'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    del = require('del'),
    uglifycss = require('gulp-uglifycss'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    scsslint = require('gulp-sass-lint'),
    jshint = require('gulp-jshint'),
    replace = require('gulp-replace'),
    rename = require('gulp-rename'),
    now = new Date().getTime();

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
    viewsSrc: 'src/AppBundle/Resources/views',
    webAssets: 'web/assets/' + now,
    production: true
};


// Set to development
gulp.task('set-development', function (done) {
    config.production = false;
    done();
});

// Clean out old assets
gulp.task('clean', function (done) {
    del([
        'web/assets/*',
        config.viewsSrc + '/Css/*'

    ]);
    done();
});

// sass quality control
gulp.task('lint.sass', function() {
    return gulp.src([
        config.sassSrc + '/**/*.scss',
        config.sassSrc + '/*.scss'])
        .pipe(scsslint());
});

// js quality control
gulp.task('lint.js', function () {
    return gulp.src([config.jsSrc + '/**/*.js','!'+ config.jsSrc + '/**/details.polyfill.js'])
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

gulp.task('sass.formatted-report', gulp.series(gulp.parallel('clean', 'lint.sass'), function () {
    return gulp.src(config.sassSrc + '/formatted-report.scss')
        .pipe(!config.production ? sourcemaps.init() : gutil.noop())
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(!config.production ? sourcemaps.write('./') : gutil.noop())
        .pipe(config.production ? uglifycss() : gutil.noop())
        .pipe(gulp.dest(config.viewsSrc + '/Css'));
}));

// Rename formatted-report.css to formatted-report.html.twig
gulp.task('rename', gulp.series('sass.formatted-report' ,function () {
    return gulp.src(config.viewsSrc + '/Css/formatted-report.css')
        .pipe(rename(config.viewsSrc + '/Css/formatted-report.css.twig'))
        .pipe(gulp.dest('./'));
}));

// Clean out old assets
gulp.task('clean-formatted-report', gulp.series('rename', function () {
    return del([
        config.viewsSrc + '/Css/formatted-report.css'
    ]);
}));

// Compile sass files
// Development builds sourcemaps
// Production minifies
gulp.task('sass', gulp.series(gulp.parallel('clean', 'lint.sass'), function () {
    return gulp.src([
            config.sassSrc + '/application.scss',
            config.sassSrc + '/application-print.scss'])
        .pipe(!config.production ? sourcemaps.init() : gutil.noop())
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(!config.production ? sourcemaps.write('./') : gutil.noop())
        .pipe(config.production ? uglifycss() : gutil.noop())
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
}));

// Copy govuk template css to stylesheets and fix image paths while we're at it (make them absolute)
gulp.task('govuk-template-css', gulp.series('clean', function () {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/stylesheets/fonts.css',
            './node_modules/govuk_template_mustache/assets/stylesheets/govuk-template.css',
        ])
        .pipe(replace('images/', '/images/'))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
}));

// fonts
gulp.task('fonts', gulp.series('clean', function () {
    return gulp.src('node_modules/govuk_template_mustache/assets/stylesheets/fonts/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/fonts'));
}));

// Copy all images
gulp.task('images', gulp.series('clean', function () {
    return gulp.src([
        './node_modules/govuk_frontend_toolkit/images/**/*',
        './node_modules/govuk_template_mustache/assets/images/*',
        './node_modules/govuk_template_mustache/assets/stylesheets/images/**/*',
        './node_modules/govuk_template_mustache/assets/stylesheets/images/gov.uk_logotype_crown.png',
        config.imgSrc + '/**/*'])
        .pipe(gulp.dest('./web/images'));
}));

// Concats js into application.js
// Production minifies
gulp.task('js.application', gulp.series(gulp.parallel('lint.js', 'clean'), function () {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/show-hide-content.js',
            config.jsSrc + '/govuk/polyfill/*.js',
            config.jsSrc + '/modules/*.js',
            config.jsSrc + '/main.js'])
        .pipe(concat('application.js'))
        .pipe(config.production ? uglify() : gutil.noop())
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
}));

// jQuery copied
gulp.task('js.other', gulp.series('clean', function() {
    return gulp.src([
        './node_modules/jquery/dist/jquery.min.js'])
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
}));

// Prepare and build all assets
gulp.task('default', gulp.parallel('clean', 'sass', 'sass.formatted-report', 'govuk-template-css', 'images', 'fonts', 'js.application', 'js.other', 'rename', 'clean-formatted-report'));
// Prepare and build all assets in Development mode
gulp.task('development', gulp.parallel('set-development', 'default'));

// Watch sass, images and js and recompile as Development
gulp.task('watch', gulp.series('development', function () {
    gulp.watch([
        config.sassSrc + '/**/*',
        config.sassSrc + '/*',
        config.imgSrc + '/**/*',
        config.jsSrc + '/**/*.js',
        config.jsSrc + '/*.js'],
        { interval: 1000 },
        gulp.series('development'));
}));
