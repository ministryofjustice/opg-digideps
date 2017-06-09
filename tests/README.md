# Digideps - Behat Testing

The BEHAT tests have 2 functions. There initial function was to provide a way to test 
stories and flows and provide integration testing. As the platform evolves more unit tests 
are created but there are still areas that need end to end testing.

In addition to initially testing code using a headless, none Javascript browser, the 
tests are now evolving to allow testing with real browser in order to test javascript 
led functionality, such as the auto save features.

## Fixtures and snapshots
Look at `DbTrait` for steps that save and restore the application status, consisting
in a postgres database snapshot and restore, allowing to test multiple 
execution paths without re-running the same steps.
Look at `behat-debug` script in the `opg-digi-deps-docker` repo as an example to debug a single scenario.
Test suites can be executed separately. Each test suite needs a fresh db with default admin user, 
refer to `test*` scripts in the `opg-digi-deps-docker` repository to understand more.

## Tags
A small note about tags. Tags have been used to mark the main 'paths' that needed to 
be tested, so the main 2 tags for this are @admin and @deputy. If you run clienttest.sh 
with no parameters it will run all the @deputy tests in a headless browser and will 
ignore any tests tagged with @javascript

Any test that needs to be tested with a real browser is flagged with @javascript

## Running Tests

### All Tests
To simply run the headless tests, setup your environment with Docker and then run test.sh 
the opg-digi-deps-docker folder

### Admin Tests
If you wish to run tests just for the admin area then whilst in opg-digi-deps-docker type:

    docker-compose run --rm api sh scripts/resetdb.sh
    docker-compose run --rm test sh scripts/admintest.sh

### Deputy Tests
If you wish to run tests just for functionality then whilst in opg-digi-deps-docker type:

    docker-compose run --rm api sh scripts/resetdb.sh
    docker-compose run --rm test sh scripts/clienttest.sh

### Other Sub Tests
There are also tags used for other 'sub' sections of tests, e.g. safeguarding. You can run those tests 
by entering 

    docker-compose run --rm test sh scripts/clienttest.sh safeguarding
    
## Real Browser Testing

The scripts have been written in such a way that a developer can run BEHAT tests with a 
real browser, either locally using Selenium or remotely using Browser Stack. By default BEHAT will 
run all tests tagged with @javascript

### Selenium/Firefox

Before trying this, first install Java and download the Selenium Webdriver server jar 
and run the jar so it sets up a local selenium server on your machine. Then: 
    
    docker-compose run --rm api sh scripts/resetdb.sh
    docker-compose run --rm \
        -e FRONTEND_NONADMIN_HOST=https://digideps-client.local \
        -e FRONTEND_ADMIN_HOST=https://admin-digideps-client.local:8080 \
        -e WD_HOST=172.16.22.243:4444/wd/hub \
        -e PROFILE=firefoxlocal \
        test sh scripts/clienttest.sh browser
        
Substitute the *WD_HOST* value with the ip and port of your local machine. When this is 
executed it will run the scripts in a Docker container, but will talk to the WD_HOST 
address, your native machine, to drive the browser. Also substitute the urls values 
with the url to reach your docker container.

### Browserstack
Browserstack provides an online resource to run all your tests with a wide variety of 
browsers. In order for browserstack to run it's tests your environment needs to be 
accessible from Browserstack, either via their reverse tunnel tool or expose your 
running instance via a public address, as is done for feature builds.

    docker-compose run --rm api sh scripts/resetdb.sh
    docker-compose run --rm \
        -e FRONTEND_NONADMIN_HOST=https://opg.scropt.com \
        -e FRONTEND_ADMIN_HOST=https://opg-admin.scropt.com \
        -e BROWSERSTACK_USER=fredsmithsmibble \
        -e BROWSERSTACK_KEY=2j34hk2jhkadjhkufh \
        -e PROFILE=chrome \
        test sh scripts/clienttest.sh browser

This will start the tests running, using the browserstack user and access key and will tell 
the browser to access your server via the named addresses. Like local browser testing, the 
tests are driven by your local docker container, which in turn talks to the frontend, api 
and database containers, but the browser accesses the service externally, like any other 
browser, possible values for profile are:

* chrome
* safari
* firefox
* ie8
* ie9
* ie10
* ie11

More profiles and devices will be added in time.
