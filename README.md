#Complete the deputy report (Client)

beta version

## Thank You

## Gulp
The frontend components rely on Gulp to be built and assembled. The main tasks involved in this part of the build involve copying image assets, compiling SASS to CSS and concatanating JS into a single file and then running uhlify to minify it.

React JS and Redux are used for the Money Transfers section and this requires transpiling from ES6/JSX into regular javascripts

Within the Gulp file the 2 main targets of interest are the default method that is used to build things in the minified form, and a 'watch' target that watches files for changes and re-compiles them on th fly.

If you are working on front end assets and are running the platorm in docker then connect to the docker container:

docker exec --it container bash

Once in the container either just type 'gulp' to recompile or type 'gulp watch' to run in development mode.

With special thanks to [BrowserStack](https://www.browserstack.com) for providing cross browser testing.
