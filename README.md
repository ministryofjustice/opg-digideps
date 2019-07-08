# Complete the deputy report

This app is the API for the [Complete the deputy report][service] service. It provides the interface used for the [frontend application][repo-client] and the deputy report database.

## Getting started

See the [Docker configuration][repo-docker] repository for instructions on how to set up the API and client containers locally.

### Related repos

- [Client][repo-client]
- [Infrastructure][repo-infra]
- [Docker configuration (private)][repo-docker]

## Development

### Authentication

Authenticate via `/auth/login`. You will need to set the client token header and provide user credentials, and will be given an AuthToken in the response header.

You will need to use the AuthToken in subsequent requests.

### API return codes

- 404 not found
- 403 Missing client secret, or invalid permissions (configuration error) or invalid ACL permissions for logged user
- 419 AuthToken missing, expired or not matching (runtime error)
- 423 Too many login attempts, Locked
- 421 User registration: User and client not found in casrec
- 422 User registration: email already existing
- 424 User registration: User and client found, but postcode mismatch
- 425 User registration: Case number already used
- 498 wrong credentials at login
- 499 wrong credentials at login (after many failed requests)
- 500 generic error due to internal exception (e.g. db offline)

### Endpoint conventions

Example with `account` (type) and `ndr` (parent type) entities

 * Get account records (ndr ID=1): `GET /ndr/1/account`
 * Add account to Ndr with ID=1: `POST /ndr/1/account`
 * Get account with id=2:  `GET /ndr/account/2`
 * Edit account with id=2: `PUT /ndr/account/2`
 * Delete account with id=2: `DELETE /ndr/account/2`

### JMS groups

We use JMS Serializer's [Groups functionality][jms-groups] to group entity properties. When querying, we can specify a group to ensure that the smallest set of data possible is retrieved.

## Testing

We use unit tests written with PHPUnit. You can run all tests via the docker container. Note that the first command sets up the database and only needs to be run once.

```sh
docker-compose run --rm api sh scripts/phpunitdb.sh
docker-compose run --rm api sh scripts/apiunittest.sh
```

We use [Mockery][mockery] to mock classes and entities which are not being tested.

## Deployment

_See [deployment documentation][docs-deployment]_

## Built with

- Symfony 3.4
- Doctrine 2.0
- PHPUnit 4

## Xdebug

Xdebug is installed on the container when your local `.env` file in the `opg-digi-deps-docker` repo contains `REQUIRE_XDEBUG_API=true`. (See the `opg-digi-deps-docker` README for more information).

Once installed, you can set xdebug config values from `api.env`. For the values to take effect, the env file must contain `OPG_PHP_XDEBUG_ENABLED=true`. The default values currently set are those required to step through the PHPSTORM IDE on a Mac. You will need to configure your IDE, ensuring that the same port is used in the IDE as that set in the `admin.env` and `frontend.env` files.

Use Postman to hit the API directly when debugging endpoints.

## License

The OPG Digideps API is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).

[repo-client]: https://github.com/ministryofjustice/opg-digi-deps-client
[repo-infra]: https://github.com/ministryofjustice/digideps-infrastructure
[repo-docker]: https://github.com/ministryofjustice/opg-digi-deps-docker
[service]: https://complete-deputy-report.service.gov.uk/
[jms-groups]: https://www.jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#creating-different-views-of-your-objects
[mockery]: http://docs.mockery.io/en/latest/
[docs-deployment]: https://github.com/ministryofjustice/opg-digi-deps-client/blob/master/docs/DEPLOYMENT.md
