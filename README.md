#Complete the Deputy Report (API)

## Overview

This app is the client used by deputy to submit their report to OPG.


Repositories
 - [Client](https://github.com/ministryofjustice/opg-digi-deps-client)
 - [API](https://github.com/ministryofjustice/opg-digi-deps-client)
 - [Docker config (private)](https://github.com/ministryofjustice/opg-digi-deps-docker)

## Frameworks and languages

- Symfony 2.8
- Doctrine 2.0
- Behat 3
- PHPUnit 4

## Setup

See the [Docker config](https://github.com/ministryofjustice/opg-digi-deps-docker) repository for instructions on how to set up the API and client containers locally.

To perform common tasks like resetting the database or installing fixtures, you can either use the `dd-` commands supplied by the Docker config, or run the scripts in the `scripts` directory on the container.

## Authentication endpoint
Authenticate via `/auth/login`. You will need to set the client token header and provide user credentials, and will be given an AuthToken in the response header.

You will need to use the AuthToken in subsequent requests.

## API return codes

* 404 not found
* 403 Missing client secret, or invalid permissions (configuration error) or invalid ACL permissions for logged user
* 419 AuthToken missing, expired or not matching (runtime error)
* 423 Too many login attempts, Locked
* 421 User regisration: User and client not found in casrec
* 422 User regisration: email already existing
* 424 User regisration: User and client found, but postcode mismatch
* 425 User regisration: Case number already used
* 498 wrong credentials at login
* 499 wrong credentials at login (after many failed requests)
* 500 generic error due to internal exception (e.g. db offline)

## Endpoint conventions

Example with `account` (type) and `ndr` (parent type) entities

 * Get account records (ndr ID=1): `GET /ndr/1/account`
 * Add account to Ndr with ID=1: `POST /ndr/1/account`
 * Get account with id=2:  `GET /ndr/account/2`
 * Edit account with id=2: `PUT /ndr/account/2`
 * Delete account with id=2: `DELETE /ndr/account/2`


## Notes about JMS groups
For an entity named `Abc`, use the group `abc` for the properties (except the relationships).

Same with entity `Xyz` where properties have the JMS group `xyz`.

## Coding standards

[PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)

## License

The OPG Digideps API is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).





