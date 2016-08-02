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

Setup local environment following instructions on the docker repository.

`app/config/parameters.yml` is generated via docker init scripts.

If installed locally, use scripts under `/scripts` to recreate db and add initial fixtures


## Authentication endpoint
via    `/auth/login`: (
needs Client token header and credentials, responds with AuthToken to send for subsequent requests).

Some endpoints are open in the firewall for special functionalities without being logged. 
Client secret is required for those.
    

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

## Notes about JMS groups
For an entity named `Abc`, use the group `abc` for the properties (except the relationships).

Same with entity `Xyz` where properties have the JMS group `xyz`.

If `Abc` has a relationship 1:N with `Xyz`, then add the group `xyz` to the 