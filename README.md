# Complete the deputy report

This app is the [Complete the deputy report][service] service. It provides an online reporting service used by deputies to submit their reports, and the private area for case managers to review submitted reports.

## Requirements

You must have Docker installed and access to the Ministry of Justice VPN.

## Installation

- Add `127.0.0.1 digideps-client.local digideps-admin.local digideps-api.local` to `/etc/hosts`
- Log into the MoJ VPN
- Run `docker login https://registry.service.opg.digital/` with username `opguk-ro` (ask your team for the password)
- Navigate to the root directory of this repository and run `docker-compose up -d`
- Check `https://digideps-client.local/` (Deputy area) and `https://digideps-admin.local/` (Admin area). Your browser will warn you about a self-signed certificate.

### Reset the database

```sh
docker-compose run --rm api sh scripts/resetdb.sh
```

## Terraform installation

To develop with Terraform, you must have the following installed:

- Docker
- Make
- terraform-docs
- jq
- aws-vault for credentials handling (optional)
- direnv (to set shell exports, see .envrc) (optional)

You can then use the make files in `environment` and `shared` to set up the environment.

```bash
# ensure your environment is setup:
export TF_WORKSPACE=myawesomeenvironment
export TF_VAR_OPG_DOCKER_TAG=1.0.myawesometag
export AWS_ACCESS_KEY_ID=AKIAEXAMPLE
export AWS_SECRET_ACCESS_KEY=cbeamsglittering
cd environment
make

# alternatively, using aws-vault:
export TF_WORKSPACE=myawesomeenvironment
export TF_VAR_OPG_DOCKER_TAG=1.0.myawesometag
cd environment
aws-vault exec identity make
```

## Testing

_See [testing documentation](docs/TESTING.md)_

## Deployment

_See [deployment documentation](docs/DEPLOYMENT.md)_

## Built with

- Terraform 0.12
- PHP 7.3
- Symfony 3.4
- Doctrine 2.0
- Twig
- Behat 3
- PHPUnit 4
- [GOV.UK Design System](https://design-system.service.gov.uk/)

## Xdebug

To use Xdebug in the `frontend` and/or `admin` app, it must be installed on the client image. To install, you will need to create a `.env` file in the top-level of this repo, and add the following:

```
REQUIRE_XDEBUG_API=false
REQUIRE_XDEBUG_FRONTEND=true
```
Then add the following to `client/docker/env/admin.env` and `client/docker/env/frontend.env`:
```
OPG_PHP_XDEBUG_ENABLED=true
OPG_PHP_XDEBUG_REMOTE_HOST=docker.for.mac.localhost
OPG_PHP_XDEBUG_REMOTE_PORT=9001
OPG_PHP_XDEBUG_IDEKEY=PHPSTORM
```
**Note** the above is an example for PHP Storm using a Mac. You will need to configure your IDE, ensuring that the same port is used in the IDE as that set above.

Now build the image and run the container. You can confirm installation by running `php -v` in the container and seeing that it reports the Xdebug version.

To install Xdebug on the API, set the flag to true in the `.env` file (see above), and add the same config as above to `api/docker/env/api.env`. **Note** that this impacts local performance dramatically and often times out when hitting the application through the frontend, so API debugging is best done in isolation by hitting endpoints via Postman, and uninstalling Xdebug when finished by setting the flag in `.env` to false.

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).

[repo-api]: https://github.com/ministryofjustice/opg-digi-deps-api
[repo-infra]: https://github.com/ministryofjustice/digideps-infrastructure
[repo-docker]: https://github.com/ministryofjustice/opg-digi-deps-docker
[service]: https://complete-deputy-report.service.gov.uk/
