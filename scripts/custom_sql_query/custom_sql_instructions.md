## How to run custom queries

You will need aws-vault and operator permissions.

You can then perform the commands required using a docker wrapper container wrapped by a make file.

Full details about how this works here: [Custom SQL Details](../../lambdas/functions/custom_sql_query/custom_sql_query.md)

Remember to edit the SQL and validation scripts in this folder.

Example make commands:

```
aws-vault exec identity -- make sql-custom-command-insert workspace=ddls1234000 before=1 after=0
aws-vault exec identity -- make sql-custom-command-get workspace=ddls1234000 id=1
aws-vault exec identity -- make sql-custom-command-sign-off workspace=ddls1234000 id=1
aws-vault exec identity -- make sql-custom-command-execute workspace=ddls1234000 id=1
aws-vault exec identity -- make sql-custom-command-revoke workspace=ddls1234000 id=1
```
