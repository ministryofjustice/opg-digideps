## AWS Console Database Access

Cloud 9 is an Amazon hosted IDE with terminal and storage.
With the correct network configuration it can be used to access RDS databases

### Create a Cloud 9 environment

Full instructions for setting up a Cloud 9 environment can be found here:
https://docs.aws.amazon.com/cloud9/latest/user-guide/create-environment.html#create-environment-main

Ensure you use values below for network settings:
```
Development account:
    vpc:    vpc-daa790be
    subnet: subnet-a61455fe
Production account:
    vpc:    vpc-4d6c7529
    subnet: subnet-80cb2dc9
```

### Install postgres client & php pdo extension

In the Cloud 9 environment, open a terminal tab & install:
```bash
sudo yum install postgresql php56-pgsql -y
```

### Install composer

```bash
cd /tmp && curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
```

### Run composer

```bash
composer install
```
It will ask you to set parameters. Set database host to postgres.production02.internal, and add database credentials for production database. Leave the others as default (just press enter)

### Clone the API repo

```bash
git clone https://github.com/ministryofjustice/opg-digi-deps-api.git
```

### Connect to database

```bash
psql -h postgres.<environment name>.internal -U digidepsmaster api
```
