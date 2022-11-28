## IAM Database Authentication

### How it is set up

To authenticate to the DB using IAM auth, we need to follow a number of steps.

1) `iam_database_authentication_enabled` needs to be set to true on the RDS cluster. This allows iam auth to happen.
2) An iam user which we connect as needs to be given rds_iam access and created on the DB. They need to be made owner
of the public schema so that they can perform migrations
```commandline
psql -c "CREATE USER iamuser LOGIN;"
psql -c "GRANT rds_iam TO iamuser;"
psql -c "ALTER SCHEMA public OWNER TO iamuser"
for tbl in `psql -qAt -c "select tablename from pg_tables where schemaname = 'public';"` ; do  psql -c "alter table \"$tbl\" owner to iamuser"; done
for tbl in `psql -qAt -c "select sequence_name from information_schema.sequences where sequence_schema = 'public';"`; do  psql -c "alter sequence \"$tbl\" owner to iamuser"; done
for tbl in `psql -qAt -c "select table_name from information_schema.views where table_schema = 'public';"`; do  psql -c "alter view \"$tbl\" owner to iamuser"; done
```
3) Whatever role is making the connection to the database must have the following permissions:

```commandline
statement {
    sid    = "ConnectToRdsByIam"
    effect = "Allow"

    actions = [
      "rds-db:connect",
    ]

    resources = ["arn:aws:rds-db:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:dbuser:*/*"]
}
```
If this is cloud9 you must use an instance profile to connect in this way!

4) You must get a token and store it somewhere. To get the token we use:
```
$RdsAuthGenerator = new AuthTokenGenerator($provider);
$token = $RdsAuthGenerator->createToken($params['host'].':'.$params['port'], 'eu-west-1', $params['user']);
```

We store this in redis with an expiry shorted than the timeout for the token.
We can then check for it's existence and use it if it exists or refresh it if not.
The user thus becomes `iamuser` and the password is the termporary generated token.
To do this we wrap the connection class with our own connection logic.

5) You need certificates added to your client and SSL mode to be set to `verify-full`

### Issues

The main issue is that it's slightly slower to connect. We have timed each element of the connection
and the difference is from running `$this->_conn = $this->_driver->connect($this->params);`. This is not any
new functionality that we have added so it seems the process is merely slower connecting using IAM auth.

There may be a way to improve the speed of this by overriding the driver.
