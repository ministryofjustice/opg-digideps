# Complete the deputy report (Client)

## Gulp
The frontend components rely on Gulp to be built and assembled. The main tasks involved in this part of the build copy image assets, compile SASS to CSS and concatinate JS into a single file and then running uglify to minify it.

React JS and Redux are used for the Money Transfers section and this requires transpiling from ES6/JSX into ES5 Javascript. Note that IE8 does not fully support ES5 so money transfers uses a none Javascript version for IE 8 and none Javascript browsers.  More info can be found [this blog post](https://www.scropt.com/2016/03/16/writing-money-transfers-in-react-part-1/).

The Gulp buid file has many targets, but the 2 that are of most interest are **default** and **watch**.

If you use Docker whilst developing the frontend, the best way to work with these assets is to connect to the docker frontend container:

    docker exec --it opgdigidepsdocker_frontend_1 bash

Once in the container you can simply enter:

    gulp watch

This will compile all the assets, in debug mode, and will then continue to watch for changes to those files until you stop it with CTRL+C.

Each of the steps in Gulp are documented in the Gulpfile.

## Browser Testing

There are notes in the readme file in the test folder to describe the best way to run regular tests and how to attempt to run those same tests with a real browser via browserstack.

With special thanks to [BrowserStack](https://www.browserstack.com) for providing cross browser testing.


## Dependencies

A brief note about dependencies. First, although we use node 4.x when building containers we also specify NPM version 3. This version of NPM has a number of important improvements over NPM version 2 which is bundled with node 4, the main one being directory structure.

Dependencies are veersioned to avoid accidently breaking the build. From time to time new review those dependencies to see if a valid new version is available, the chief of these should be [govuk-elements-sass](https://www.npmjs.com/package/govuk-elements-sass)
