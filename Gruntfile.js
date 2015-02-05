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
                ]
            }
        },

        watch: {
            options: {
                nospawn: true
            },
            scss: {
                files: [scssPath + '{,*/}*.scss'],
                tasks: ['sass']
            }
        },

        copy: {
            copyStylesheets: {
                cwd: 'bower_downloads/govuk_frontend_toolkit/stylesheets',
                src: ['**/*'],
                dest: 'src/AppBundle/Resources/assets/scss',
                expand: true
            },
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
            copyGovElements: {
                cwd: 'bower_downloads/govuk_elements/public/sass/elements',
                src: ['**/*'],
                dest: 'src/AppBundle/Resources/assets/scss/elements',
                expand: true
            },
            copyJquery: {
                cwd: 'bower_downloads/jquery/dist',
                src: ['**/*'],
                dest: 'web/javascripts/vendor/jquery',
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
