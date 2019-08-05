'use strict';

const gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    del = require('del'),
    uglifycss = require('gulp-uglifycss'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    scsslint = require('gulp-sass-lint'),
    jshint = require('gulp-jshint'),
    now = new Date().getTime(),
    postcss = require('gulp-postcss');

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
    webAssets: 'web/assets/' + now,
};

const cleanAssets = () => { // Clear web assets folder
    return del([
        'web/assets/*'
    ]);
}

const lintSass = () => { // sass quality control
    return gulp.src([
        config.sassSrc + '/**/*.scss',
        config.sassSrc + '/*.scss'])
        .pipe(scsslint({
            options: {
                configFile: '.sass-lint.yml'
            }
        }))
        .pipe(scsslint.format());
}

const lintJS = () => { // JS quality control
    return gulp.src([config.jsSrc + '/**/*.js'])
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
}

const buildApplicationCSSFromSass = () => { // Compile sass files, uglify, copy
    return gulp.src([
            config.sassSrc + '/application.scss',
            config.sassSrc + '/formatted-report.scss',
        ])
        .pipe(sourcemaps.init())
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(uglifycss())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
};

const copyGovUKFonts = () => {
    return gulp.src('node_modules/govuk-frontend/assets/fonts/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/fonts'));
}

const copyAllImages = () => {
    return gulp.src([
            './node_modules/govuk_frontend_toolkit/images/**/*',
            './node_modules/govuk-frontend/assets/images/*',
            config.imgSrc + '/**/*'
        ])
        .pipe(gulp.dest('./web/images'));
}

const concatJSThenMinifyAndCopy = () => { // Only minify if prod
    return gulp.src([
            './node_modules/govuk-frontend/all.js',
            config.jsSrc + '/modules/*.js',
            config.jsSrc + '/main.js'])
        .pipe(sourcemaps.init())
        .pipe(concat('application.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
}

const copyJQuery = () => {
    return gulp.src([
        './node_modules/jquery/dist/jquery.min.js'])
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
}

const checkCSSAccessibility = () => {
    return gulp.src(config.webAssets + '/stylesheets/*.css')
      .pipe(
        postcss([
            require('postcss-wcag-contrast')({compliance: 'AA'})
        ])
      );
}

gulp.task('sass', gulp.series(lintSass, buildApplicationCSSFromSass));

gulp.task('app-js', gulp.series(lintJS, concatJSThenMinifyAndCopy));

gulp.task('lint', gulp.series(lintSass, lintJS, checkCSSAccessibility));

// Prepare and build all assets.
gulp.task('default', gulp.series(
    cleanAssets,
    gulp.parallel(
        'sass',
        copyAllImages,
        copyGovUKFonts,
        'app-js',
        copyJQuery,
    ), checkCSSAccessibility));

// Watch sass, images and js and recompile as Development
gulp.task('watch', gulp.series(function () {
    gulp.watch([
        config.sassSrc + '/**/*',
        config.sassSrc + '/*',
        config.imgSrc + '/**/*',
        config.jsSrc + '/**/*.js',
        config.jsSrc + '/*.js'],
        { interval: 1000, usePolling: true },
        gulp.series('default'));
}));
