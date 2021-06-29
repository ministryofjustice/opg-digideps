[![codecov](https://codecov.io/gh/ministryofjustice/opg-digideps/branch/master/graph/badge.svg?token=asprWvTRqh)](https://codecov.io/gh/ministryofjustice/opg-digideps)

# Complete the deputy report

This app is the [Complete the deputy report][service] service. It provides an online reporting service used by deputies to submit their reports, and the private area for case managers to review submitted reports.

## Requirements

You must have Docker installed.

If developing the app then ensure you have [pre-commit](https://pre-commit.com/) installed to take advantage of the pre-commit [hooks](.pre-commit-config.yaml) we've added to the project to make PRs a more consistent and enjoyable experience.

## Installation

- Add `127.0.0.1 digideps.local admin.digideps.local api.digideps.local www.digideps.local` to `/etc/hosts`
- Navigate to the root directory of this repository and run `make up-app`
- Check `https://digideps.local/` (Deputy area) and `https://admin.digideps.local/` (Admin area). Your browser will warn you about a self-signed certificate.
- Run `./generate_certs.sh` to populate your certs directory

### Reset the database

Recreate DB schema and run migrations:

```shell script
$ make reset-database
```

Wipe DB contents and re-run fixtures:

```shell script
$ make reset-fixtures
```

## Traffic Flow Diagram

![Digideps traffic flow diagram](./docs/traffic_flow_diagram.png)

This diagram can be updated in `traffic_flow_diagram.puml` and then rendered to PNG using `plantuml ./docs/traffic_flow_diagram.puml -o .`.

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
export TF_VAR_OPG_DOCKER_TAG=mybranch-githash
export AWS_ACCESS_KEY_ID=AKIAEXAMPLE
export AWS_SECRET_ACCESS_KEY=cbeamsglittering
cd environment
make

# alternatively, using aws-vault:
export TF_WORKSPACE=myawesomeenvironment
export TF_VAR_OPG_DOCKER_TAG=mybranch-githash
cd environment
aws-vault exec identity make
```

## Testing & Debugging

_See [testing](docs/TESTING.md)_ and [debugging ](docs/DEBUGGING.md) documentation.

## Deployment

_See [deployment documentation](docs/DEPLOYMENT.md)_

## Built with

- Terraform 0.14
- PHP 7.4
- Symfony 4.4
- Doctrine 2.0
- Twig
- Behat 3
- PHPUnit 8
- [GOV.UK Design System](https://design-system.service.gov.uk/)
- [GOV.UK Notify](https://notifications.service.gov.uk/)

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).

[service]: https://complete-deputy-report.service.gov.uk/

## Runbook

Our runbook, incident response process and other OPG technical guidance can be found [here](https://ministryofjustice.github.io/opg-technical-guidance/#opg-technical-guidance).
