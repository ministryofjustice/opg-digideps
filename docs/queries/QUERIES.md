# Queries

### SQL queries

The purpose of this folder is to store any queries that the Digideps Team have used.

This can include queries used to resolve user issues, or used for reporting purposes and can be run
against the digideps database.

To access the database for running a direct query, you must go to our shared cloud9 instance in
`preproduction` and connect to psql reader instance:

```commandline
psql -h reader-instance-connection-string -U user-name api
```

You should replace reader-instance-connection-string and user-name from the above
with relevant parameters from RDS tab of AWS console. Make sure you use the reader instance for safety.

### Postgres command line

Please see documentation for full postgres instructions. Here we have a few useful bits of information
to get you around if you are new to the DB.
- `\dt` shows all tables
- `\x auto` set expanded display to auto so text doesn't wrap

To find out how tables are linked you can use the below query and change where clause to be either target_table
or source_table:

```
SELECT *
FROM
(
	SELECT
	  o.conname AS constraint_name,
	  (SELECT nspname FROM pg_namespace WHERE oid=m.relnamespace) AS source_schema,
	  m.relname AS source_table,
	  (SELECT a.attname FROM pg_attribute a WHERE a.attrelid = m.oid AND a.attnum = o.conkey[1] AND a.attisdropped = false) AS source_column,
	  (SELECT nspname FROM pg_namespace WHERE oid=f.relnamespace) AS target_schema,
	  f.relname AS target_table,
	  (SELECT a.attname FROM pg_attribute a WHERE a.attrelid = f.oid AND a.attnum = o.confkey[1] AND a.attisdropped = false) AS target_column
	FROM
	  pg_constraint o LEFT JOIN pg_class f ON f.oid = o.confrelid LEFT JOIN pg_class m ON m.oid = o.conrelid
	WHERE
	  o.contype = 'f' AND o.conrelid IN (SELECT oid FROM pg_class c WHERE c.relkind = 'r')
) AS s1
WHERE target_table = 'my_table';
```

### Cloudwatch queries

There are also a number of queries that can be run against cloudwatch logs that any user
with viewer permissions is able to run. To access the them:
- Log in to AWS console
- Search for cloudwatch and click on it
- On the menu down the left side, click on logs insights
- On the menu on the right hand side, click on Queries
- The business queries are all under the folder, Business Analytics
- Choose one, choose the time frame (top right) and click Run Query
