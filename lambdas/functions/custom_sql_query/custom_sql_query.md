## Custom SQL Query Instructions

### Local Setup

We have packaged this lambda as an image. Some of our other simpler lambdas are packaged as zips for convenience.
There a couple of reasons for making this an image.

1. We don't want the code to be editable in the lambda window for added security
2. More consistent local environment
3. We use psycopg2 which would require lambda layers which we don't want to maintain

```
curl -XPOST "http://localhost:9070/2015-03-31/functions/function/invocations" -d '@./lambdas/functions/custom_sql_query/insert.json' | jq
```
