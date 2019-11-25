# Complete the deputy report

This app is the [Complete the deputy report][service] service. It provides an online reporting service used by deputies to submit their reports, and the private area for case managers to review submitted reports.

## Requirements

You must have Docker installed.
Clone [opg-file-scanner-service](https://github.com/ministryofjustice/opg-file-scanner-service) into `../opg-file-scanner-service`

## Installation

- Add `127.0.0.1 digideps.local admin.digideps.local api.digideps.local www.digideps.local` to `/etc/hosts`
- Navigate to the root directory of this repository and run `docker-compose up -d`
- Check `https://digideps.local/` (Deputy area) and `https://admin.digideps.local/` (Admin area). Your browser will warn you about a self-signed certificate.
- Run `./generate_certs.sh` to populate your certs directory

### Reset the database

```sh
docker-compose run --rm api sh scripts/resetdb.sh
```

## Traffic Flow Diagram

![Digideps traffic flow diagram](./docs/traffic_flow_diagram.png)

This diagram can be updated in `traffic_flow_diagram.puml` and then rendered to PNG using `plantuml ./docs/traffic_flow_diagram.puml -o ./docs`.

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

## Testing & Debugging

_See [testing](docs/TESTING.md)_ and [debugging ](docs/DEBUGGING.md) documentation.

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

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).

[repo-api]: https://github.com/ministryofjustice/opg-digi-deps-api
[repo-infra]: https://github.com/ministryofjustice/digideps-infrastructure
[repo-docker]: https://github.com/ministryofjustice/opg-digi-deps-docker
[service]: https://complete-deputy-report.service.gov.uk/
