"use strict";

module.exports = function (grunt) {

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
                        cwd: scssPath,
                        src: ['*.scss'],
                        dest: 'web/stylesheets',
                        ext: '.css'
                    },
                    {
                        expand: true,
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

        watch: {
            options: {
                nospawn: true
            },
            scss: {
                files: [scssPath + '/**/*.scss'],
                tasks: ['sass'],
                options: {
                    livereload: true
                }
            },
            js: {
                files: [jsPath + '/**/*.js'],
                tasks: ['concat:dist', ]
            }
        },
        concat: {
            dist: {
                src: ['src/AppBundle/Resources/assets/javascripts/expanding.js', 
                    'src/AppBundle/Resources/assets/javascripts/sessionTimeoutDialog.js',
                    'src/AppBundle/Resources/assets/javascripts/sortcode.js',
                    'src/AppBundle/Resources/assets/javascripts/gdsbuttons.js'],
                dest: 'web/javascripts/application.js',
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
    grunt.registerTask('default', ['clean', 'copy', 'sass','concat:dist']);
    grunt.registerTask('build', ['default']);

};
