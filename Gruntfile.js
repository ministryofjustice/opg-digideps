module.exports = function (grunt) {
    'use strict';

    var scssPath = 'src/AppBundle/Resources/assets/scss';
    var jsPath = 'src/AppBundle/Resources/assets/javascripts';

    // Load NPM Tasks
    require('load-grunt-tasks')(grunt);


    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        clean: ["web/images", "web/javascripts", "web/stylesheets", "src/AppBundle/Resources/views/Email/css",],

        sass: {
            dist: {
                files: [
                    {
                        expand: true,
                        style: 'compressed',
                        cwd: scssPath,
                        src: ['*.scss'],
                        dest: 'web/stylesheets',
                        ext: '.css'
                    },
                    {
                        expand: true,
                        style: 'compressed',
                        cwd: 'bower_downloads/moj_template/source/assets/stylesheets',
                        src: ['*.scss'],
                        dest: 'web/stylesheets',
                        ext: '.css'
                    },
                    {
                        expand: true,
                        style: 'compressed',
                        cwd: scssPath + '/email-template',
                        src: ['*.scss'],
                        dest: 'src/AppBundle/Resources/views/Email/css',
                        ext: '.html.twig'
                    }
                ],
                options: {
                    loadPath: [
                        'bower_downloads/govuk_elements/govuk/public/sass',
                        'bower_downloads/govuk_elements/public/sass/elements'
                    ]
                }

            }
        },
        cssmin: {
            target: {
                files: [{
                    expand: true,
                    cwd: 'web/stylesheets',
                    src: ['govuk-template.css', 'fonts.css', 'moj-template.css', 'application.css',
                        'govuk-template-print.css', 'application-print.css'],
                    dest: 'web/stylesheets/min',
                    ext: '.min.css'
                }]
            }
        },
        watch: {
            options: {
                nospawn: true
            },
            scss: {
                files: [scssPath + '/**/*.scss'],
                tasks: ['sass','cssmin','concat:css','concat:print'],
                options: {
                    livereload: true
                }
            },
            js: {
                files: [jsPath + '/**/*.js'],
                tasks: ['jshint','concat:dist', ]
            }
        },
        jshint: {
            all: ['Gruntfile.js', jsPath +'/**/*.js']
        },
        concat: {
            dist: {
                src: [ 'bower_downloads/govuk_elements/govuk/public/javascripts/govuk-template.js',
                    'bower_downloads/govuk_elements/govuk/public/javascripts/govuk/selection-buttons.js',
                    'bower_downloads/moj_template/source/assets/javascripts/moj.js',
                    jsPath +'/**/*.js'],
                dest: 'web/javascripts/application.js',
            },
            css: {
                src: [
                    'web/stylesheets/min/fonts.min.css',
                    'web/stylesheets/min/govuk-template.min.css',
                    'web/stylesheets/min/moj-template.min.css',
                    'web/stylesheets/min/application.min.css'],
                dest: 'web/stylesheets/application.min.css'
            },
            print: {
                src: ['web/stylesheets/min/govuk-template-print.min.css','web/stylesheets/min/application-print.min.css'],
                dest: 'web/stylesheets/print.css'
            }
        },
        uglify: {
            js: {
                files: {
                    'web/javascripts/application.js': 
                    [ 'bower_downloads/govuk_elements/govuk/public/javascripts/govuk-template.js', 
                        'bower_downloads/govuk_elements/govuk/public/javascripts/govuk/selection-buttons.js',
                        'bower_downloads/moj_template/source/assets/javascripts/moj.js',
                        jsPath +'/**/*.js']
                }
            }
        },
        copy: {
            copyGovUkTemplateImages: {
                cwd: 'bower_downloads/govuk_elements/govuk/public/images',
                src: ['**/*'],
                dest: 'web/images',
                expand: true
            },
            copyGovUkTemplateJS: {
                cwd: 'bower_downloads/govuk_elements/govuk/public/javascripts',
                src: ['**/*'],
                dest: 'web/javascripts',
                expand: true
            },
            copyGovUkTemplateStylesheets: {
                cwd: 'bower_downloads/govuk_elements/govuk/public/stylesheets',
                src: ['**/*'],
                dest: 'web/stylesheets',
                expand: true
            },
            copyMojJS: {
                cwd: 'bower_downloads/moj_template/source/assets/javascripts',
                src: ['**/*'],
                dest: 'web/javascripts',
                expand: true
            },

            copyPlugins: {
                cwd: 'bower_downloads',
                src: ['jquery/dist/**/*'],
                dest: 'web/javascripts/vendor',
                expand: true
            },
            copyHTML5shiv: {
                cwd: 'bower_downloads/html5shiv/dist',
                src: ['**/*'],
                dest: 'web/javascripts/vendor/html5shiv',
                expand: true
            },
            copyImages: {
                cwd: 'src/AppBundle/Resources/assets/images',
                src: ['**/*' ],
                dest: 'web/images',
                expand: true
            }
        }

    });

    // Register Grunt Tasks
    grunt.registerTask('default', ['clean', 'copy', 'sass','jshint','uglify:js','cssmin','concat:css','concat:print']);
    grunt.registerTask('build', ['default']);

};
