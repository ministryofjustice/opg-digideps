# PHP Upgrade

Renovate will periodically notify us of PHP dependencies that need upgrading.

If we are unable to update our application through renovate directly because of further dependencies,
then we should try and perform the update from our branch.

#### Prerequisites:

You need composer [installed](https://getcomposer.org/doc/00-intro.md)

#### How to upgrade

1) Create a branch new branch `git checkout -b <branch name>`

2) Use https://packagist.org/ to check the dependencies and follow up the the links until you hit one of the libraries in
`composer.json` in the api/app and client/app folders.

3) Update the relevant package to the preferred version in the `composer.json`

4) Run an update on the specific dependency e.g `php composer update monolog/monolog`

5) Create a PR and check that all the unit and integration tests still work

6) If anything is broken then rerun them locally and use the logs to work out what is wrong.
It's usually another dependency that needs changing or sometimes you may have something newly deprecated
in your code that needs tweaking.

7) When all tests are passing then it can be submitted to code review.
