"use strict";

/*
 TODO
 */


module.exports = function (grunt) {

    var scssPath = 'src/AppBundle/Resources/assets/scss';

    console.log("Grunt Starting up" + scssPath);


    // Load NPM Tasks
    require('load-grunt-tasks')(grunt);


    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: scssPath,
                        src: ['*.scss'],
                        dest: 'web/css',
                        ext: '.css'
                    }
                ],
                options: {
                    loadPath: [
                        'bower_downloads/govuk_frontend_toolkit/stylesheets',
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
                tasks: ['sass']
            }
        },

        copy: {
            copyImages: {
                cwd: 'bower_downloads/govuk_frontend_toolkit/images',
                src: ['**/*'],
                dest: 'web/images',
                expand: true
            },
            copyJS: {
                cwd: 'bower_downloads/govuk_frontend_toolkit/javascripts',
                src: ['**/*'],
                dest: 'web/javascripts',
                expand: true
            },
            copyPlugins: {
                cwd: 'bower_downloads',
                src: ['jquery/dist/**/*','jquery-validation/dist/**/*' ],
                dest: 'web/javascripts/vendor',
                expand: true
            },
            copyHTML5shiv: {
                cwd: 'bower_downloads/html5shiv/dist',
                src: ['**/*'],
                dest: 'web/javascripts/vendor/html5shiv',
                expand: true
            }
        }

    });

    // Register Grunt Tasks
    grunt.registerTask('default', ['copy', 'sass', 'watch']);

};
