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

### Upgrade to PHP 7.3
```bash
sudo yum remove php*
sudo yum install php73 php73-pdo php73-pgsql -y
```

### Install postgres client
```bash
sudo yum install postgresql
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
cd opg-digideps\api
composer install
```
It will ask you to set parameters. Set database host to postgres.environment-name.internal, and add database credentials for database. Leave the others as default (just press enter)


### Connect to database

```bash
psql -h postgres.<environment name>.internal -U digidepsmaster api
```

### Run migrations 
#### WARNING: Migrations will execute against whichever database is set in parameters.yml
```bash
cd opg-digideps\api
php app/console doctrine:migrations:status
php app/console doctrine:migrations:migrate
```
