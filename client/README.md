# Complete the deputy report

This app is the client/frontend for the [Complete the deputy report][service] service. It provides the public interface used by deputies to submit their reports, and the private area for case managers to review submitted reports.

## Getting started

See the [Docker configuration][repo-docker] repository for instructions on how to set up the API and client containers locally.

### Related repos

- [API][repo-api]
- [Infrastructure][repo-infra]
- [Docker configuration (private)][repo-docker]

## Building assets

The frontend components rely on Gulp to be built and assembled. The main tasks involved in this part of the build are copying image assets, compiling SASS to CSS and concatinating JS into a single file and then running uglify to minify it.

Assets are automatically rebuilt with Gulp when you build the frontend image. You can also run commands against the NPM image. For example, to lint all files:

```sh
docker-compose run --rm npm run lint
```

## Testing

_See [testing documentation](docs/TESTING.md)_

## Deployment

_See [deployment documentation](docs/DEPLOYMENT.md)_

## Built with

- PHP 7.3
- Symfony 3.4
- Twig
- Behat 3
- PHPUnit 4
- Uses [GOV.UK Design System](https://design-system.service.gov.uk/)

## Xdebug

Xdebug is installed on the container when your local `.env` file in the `opg-digi-deps-docker` repo contains `REQUIRE_XDEBUG_FRONTEND=true`. (See the `opg-digi-deps-docker` README for more information).

Once installed, you can set xdebug config values from `admin.env` and `frontend.env`. For the values to take effect, the env file must contain `OPG_PHP_XDEBUG_ENABLED=true`. The default values currently set are those required to step through the PHPSTORM IDE on a Mac. You will need to configure your IDE, ensuring that the same port is used in the IDE as that set in the `admin.env` and `frontend.env` files.

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).

[repo-api]: https://github.com/ministryofjustice/opg-digi-deps-api
[repo-infra]: https://github.com/ministryofjustice/digideps-infrastructure
[repo-docker]: https://github.com/ministryofjustice/opg-digi-deps-docker
[service]: https://complete-deputy-report.service.gov.uk/
