## How to run custom queries

You will need aws-vault and operator permissions.

You can then perform the commands required using a docker wrapper container wrapped by a make file.

Full details about how this works here: [Custom SQL Details](../../lambdas/functions/custom_sql_query/custom_sql_query.md)

Edit these two scripts (in this folder):

* _run.sql - should contain the SQL to perform the update/delete/insert
* _verification.sql - should contain SQL to confirm that the database has been correctly modified

**DO NOT** check in your modified versions of these files, as they may contain sensitive data.

Example make commands:

```
# add the query to the database, ready to be executed (see below for the meaning of "before" and "after")
aws-vault exec identity -- make sql-custom-command-insert workspace=ddls1234000 before=1 after=0

# check the content of the query
aws-vault exec identity -- make sql-custom-command-get workspace=ddls1234000 id=1

# sign-off the query as ready to run
aws-vault exec identity -- make sql-custom-command-sign-off workspace=ddls1234000 id=1

# execute the query
aws-vault exec identity -- make sql-custom-command-execute workspace=ddls1234000 id=1

# remove the query (this only works if you do it before executing it; once executed, you can't go back)
aws-vault exec identity -- make sql-custom-command-revoke workspace=ddls1234000 id=1
```

`before` specifies how many records should be returned by the _verification.sql query before _run.sql is run;
`after` specifies how many records should be returned by the _verification.sql query after the _run.sql has run

To run against your own environment in AWS, use your branch name (which is the environment name) as the workspace
variable. You can use 'local' as the workspace to run against local.

The real environments are development, integration, training, preproduction and production.
