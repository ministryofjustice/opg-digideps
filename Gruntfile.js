"use strict";

/*
 TODO
 */


module.exports = function (grunt) {

    // Load NPM Tasks
    require('load-grunt-tasks')(grunt);


    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: 'src/front-end/scss',
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
            css: {
                files: ['src/front-end/scss/{,*/}*.scss'],
                tasks: ['sass']
            }
        }

        /*
         copy: {
         main: {
         src: 'src/front-end/libs*//*',
         dest: '',
         options: {
         *//*process: function (content, srcpath) {
         return content.replace(/[sad ]/g, "_");
         }*//*
         }
         },
         assets: {
         cwd: 'src/front-end/libs/govuk_template_mustache/assets/',
         src: ['**'],
         dest: 'web/assets',
         expand: true,
         options: {

         }
         }
         },
         */


    });

    // Register Grunt Tasks
    grunt.registerTask('default', ['sass', 'watch']);

};
