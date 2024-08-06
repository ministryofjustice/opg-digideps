# Cross Browser Testing

We currently use [browser-stack] to provide our cross browser testing. We aim to perform the majority of tests against a real AWS environment using the goutte driver
as this has the following advantages:

- Very fast headless tests mean that we can test all valid scenarios in a timely fashion
- We pick up on AWS environment based issues
- We don't need to worry about how things are displayed on different browsers

The main drawback of using the goutte driver in this way is that we don't see javascript issues.

The way we get around this is by having a selection of selenium based tests that run against some of the more popular browsers in browser stack.

## How it works

We have a local browser stack daemon that runs and allows a tunnel that connects you with a real machine running
on browserstack servers. The daemon receives the input from the user (or our behat tests in this case) and send it across to the server.
The server gets this input, does the required operation and sends the changes back to the client.

## How to set it up

The setup is surprisingly straight forward:

- You will need an account in the digideps team in Browserstack. Only digideps developers have access to this account
and you can be added to the team on the browser stack website.

- You must find your user name and access key under the `automate` tab on browser stack website and set them in your
`./behat/.env` file as the following variables respectively:
    - `BROWSERSTACK_USERNAME`
    - `BROWSERSTACK_KEY`
- From the root of this repo, run the following command (if on mac or version without osx if on linux):
`./behat/BrowserStackLocal-osx --daemon "start" --key <your_key>`

- This starts the tunnel and you can then run against different browsers by specifying the browser:
    - `docker compose -f docker-compose.yml -f docker-compose.behat.yml run --rm test --profile v2-tests-browserstack-chrome --tags @js-basic-check`
    - `docker compose -f docker-compose.yml -f docker-compose.behat.yml run --rm test --profile v2-tests-browserstack-firefox --tags @js-basic-check`
    - `docker compose -f docker-compose.yml -f docker-compose.behat.yml run --rm test --profile v2-tests-browserstack-ie11 --tags @js-basic-check`

- To see detailed output of your runs including screenshots, videos and logs then go to the `automate` tab again
and search for your run.

All the profiles are controlled form the `behat.yml` file as usual and more can be added as needed.


## Pipeline

We have a basic setup in our pipeline though we are using the V2 rewrite to make something more concrete that we can use.
More on this soon...

[browser-stack]: https://www.browserstack.com/browser
