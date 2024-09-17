## Custom SQL Query Instructions

## Local Setup

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

## Running it locally with wrapper python script

### How permissions work

- Stored procedures have the following defined: SECURITY DEFINER
- This means that the stored procedure has the permissions of the user who created the procedure (which has read/write on public schema).
- Next we have a database user called custom_sql_user that has permissions to call the 5 procedures but nothing else.
- Access to the password for this user is stored in an aws secret and limited to the role of the lambda function.
- The lambda function has access to the DB and can connect with the custom_sql_user.
- Only users who can assume the operator role in the specific account for the lambda may invoke it.
- A wrapper script allows us to use aws-vault to temporarily include credentials and set up the requests to call this.

As such we manage the chain of credentials so that operators can only perform updates via wrapper script, never directly.

### How to use it

Currently you will need a local or global install of boto3 and requests to use the wrapper scripts.
You can do this in a virtualenv. In a future PR, we will add this to a docker container to avoid local setup issues.

Currently from the orchestration/custom_sql_query folder you can amend the example _run.sql and _verification.sql files.

You can then do your first trial either against the local lambda or against a lambda in AWS.

Example of local commands would be:

```
aws-vault exec identity -- python3 run_custom_query.py local insert --sql_file=_run.sql --verification_sql_file=_verification.sql --expected_before=1 --expected_after=0
aws-vault exec identity -- python3 run_custom_query.py local get --query_id=1
aws-vault exec identity -- python3 run_custom_query.py local sign_off --query_id=1
aws-vault exec identity -- python3 run_custom_query.py local execute --query_id=1
aws-vault exec identity -- python3 run_custom_query.py local revoke --query_id=1
```

In your branch (notice the positional argument):

```
aws-vault exec identity -- python3 run_custom_query.py ddls1234098 insert --sql_file=_run.sql --verification_sql_file=_verification.sql --expected_before=1 --expected_after=0
```

## End to end process

1) Thoroughly test your SQL in preprod and create a validation script that fulfils your needs (more on this later).
2) Notify and discuss with the team that there is a need for an adhoc query to fulfill a particular ticket and find another developer who will complete the process with you.
3) Create the insert (as above) and pass the ID to the other developer.
4) The other developer does a get and checks the query and validation queries thoroughly.
5) When other dev is happy they do the sign_off.
6) If either developer is unsure at this point about the query they can do a revoke that deletes the query before it is actioned (not possible after).
7) If both are happy then creator of the query may execute the query.
8) If the validation is correct then the query is applied. If not then query is rolled back.
