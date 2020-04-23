# AWS Console Database Access

Cloud9 is an Amazon hosted IDE with terminal and storage.
With the correct network configuration it can be used to access RDS databases

## Shared Cloud9 environment

We have a shared team cloud9 environment that is managed in terraform. Due to some limitations with terraforms AWS modules and the lack of an AWS account that can access environments the owner of the environment is set to Alex Saunders (alex.saunders@digital.justice.gov.uk). Any new users for the environment will need to be added by Alex via the share button inside the environment.

## Connect to database

In order to connect to the database run the following command and provide the DB password specific to the environment you are accessing it from (this can be found in Secrets Manager)

```bash
psql -h postgres.<environment name>.internal -U digidepsmaster api
```

## One time set up instructions

If for any reason the environment is destroyed, the below instructions should be run to set up the required parts of the app to provide DB access

**These instructions should not be run each time a new user accesses the environment.**

```
### Upgrade to PHP 7.3 and install required libs
```bash
sudo yum remove php*
sudo yum install php73 php73-pdo php73-pgsql postgresql php73-mbstring.x86_64 -y
```

### Install composer

```bash
cd /tmp && curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
```

### Clone the repo

```bash
git clone https://github.com/ministryofjustice/opg-digideps.git
```

### Run composer

```bash
cd opg-digideps/api
composer install
```
There will be a prompt to set parameters - you can just hit enter for all of the default values.

### Run migrations
#### WARNING: Migrations will execute against whichever database is set in parameters.yml
```bash
cd opg-digideps\api
php app/console doctrine:migrations:status
php app/console doctrine:migrations:migrate
```
