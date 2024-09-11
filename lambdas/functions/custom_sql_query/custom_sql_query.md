## Custom SQL Query Instructions

### Local Setup

We have packaged this lambda as an image. Some of our other simpler lambdas are packaged as zips for convenience.
There a couple of reasons for making this an image.

1. We don't want the code to be editable in the lambda window for added security
2. More consistent local environment
3. We use psycopg2 which would require lambda layers which we don't want to maintain

### Running it locally directly

```
docker compose up -d custom-sql-query
```

this will bring up the lambda and it's dependencies (localstack and postgres)

You should probably reset the DB so you have something to test with:

```
make reset-database reset-fixtures
```

We can then create a custom query:

```
curl -XPOST "http://localhost:9070/2015-03-31/functions/function/invocations" -d '@./lambdas/functions/custom_sql_query/payloads/insert_query.json' | jq
```

We can then get the custom query to look at it:

```
curl -XPOST "http://localhost:9070/2015-03-31/functions/function/invocations" -d '@./lambdas/functions/custom_sql_query/payloads/get_query.json' | jq
```

Now let's sign it off:

```
curl -XPOST "http://localhost:9070/2015-03-31/functions/function/invocations" -d '@./lambdas/functions/custom_sql_query/payloads/sign_off_query.json' | jq
```

Finally we can execute the query:

```
curl -XPOST "http://localhost:9070/2015-03-31/functions/function/invocations" -d '@./lambdas/functions/custom_sql_query/payloads/execute_query.json' | jq
```

We can also revoke but not once the query is run. You can edit the .json files in payloads folder
to play around with creating and executing the queries.

### Running it locally with wrapper python script

### How permissions work

- Stored procedures have the following defined: SECURITY DEFINER
- This means that the stored procedure has the permissions of the user who created the procedure (which has read/write on public schema).
- Next we have a database user called custom_sql_user that has permissions to call the 5 procedures but nothing else.
- Access to the password for this user is stored in an aws secret and limited to the role of the lambda function.
- The lambda function has access to the DB and can connect with the custom_sql_user.
- Only users who can assume the operator role in the specific account for the lambda may invoke it.
- A wrapper script allows us to use aws-vault to temporarily include credentials and set up the requests to call this.

As such we manage the chain of credentials so that operators can only perform updates via wrapper script, never directly.
