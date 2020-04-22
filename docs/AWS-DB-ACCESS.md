## AWS Console Database Access

You can access AWS databases through [Cloud9 environments][cloud9]. Cloud9 provides an IDE run in EC2, which has managed access to the RDS cluster.

In each AWS account, there is a shared environment called `team-cloud9-env` which is available to all operators and has required software installed.

You can then use the environment's terminal to connect to the database with `psql`:

```bash
psql -h postgres.<environment name>.internal -U digidepsmaster api
```

If necessary, you can also clone the `opg-digideps` repository into the Cloud9 environment and run commands using [Symfony's console commands][symfony-console].

[cloud9]: https://aws.amazon.com/cloud9/
[symfony-console]: https://symfony.com/doc/current/components/console.html
