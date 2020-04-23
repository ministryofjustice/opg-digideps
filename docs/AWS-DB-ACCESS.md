# AWS Console Database Access

You can access AWS databases through [Cloud9 environments][cloud9]. Cloud9 provides an IDE run in EC2, which has managed access to the RDS cluster.

In each AWS account, there is a shared environment called `team-cloud9-env` which is available to all operators and has required software installed.

You can use the environment's terminal to connect to the database with `psql`:

```bash
psql -h postgres.<environment name>.internal -U digidepsmaster api
```

You can also install the PHP application in Cloud9 and use it to run maintenance commands against the database.

## Software installed on Cloud9 environments

As an EC2 machine, you can install new software packages onto the Cloud9 environments using `yum` on the command line. The following software is already installed. The commands used to install each is included for debugging or in case you need to reinstall.

### Postgres client

As mentioned above, you can use the Postgres client to log into and query the databases in the AWS account.

```bash
sudo yum install postgresql
```

### PHP and Composer

You can use PHP to execute scripts or interact with the DigiDeps repository. For example, you can use [Symfony's console commands][symfony-console] to debug or maintain the application in context.

Composer is necessary to install third-party PHP packages like Symfony and Doctrine.

```bash
sudo yum install php73 php73-pdo php73-pgsql php73-mbstring.x86_64

cd /tmp && curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
```

### DigiDeps source code

A copy of the `opg-digideps` source code has been cloned onto the environment. You should use `git pull` before interacting to ensure it's up to date.

If you run `composer install`, you will be prompted to enter the application parameters. Set the database host to `postgres.<environment name>.internal`, and add database credentials from AWS Secrets Manager. Leave the other parameters as default (just press enter).

You can later change the parameters (such as target database) by editing `parameters.yml`.

```bash
git clone https://github.com/ministryofjustice/opg-digideps.git
cd opg-digideps/api
composer install
cd ../client
composer install
```

[cloud9]: https://aws.amazon.com/cloud9/
[symfony-console]: https://symfony.com/doc/current/components/console.html
