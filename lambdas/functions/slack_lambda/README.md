## slack lambda

The slack lambda lives in our shared environment.

We have one lambda per account that is triggered from two SNS queues (one in eu-west-1 and one for the loadbalancers in us-east-1).

It can also be triggered and accepts cloudwatch event rules.
For your cloudwatch event rule to be effective you need to supply the detail key
with the following items in json format:

```
log-group - the name of the log group to search (can be added from terraform)
log-entries - list of terms to search for (log records search need to be in format: title - success/failure - description
search-timespan - values are human readable like '1 hour' or '30 minutes'
bank-holidays - string of True of False if you want it to run on bank holidays or not
```

The final way this is called is with the wrapper ci_slack which allows it to be called from the
github actions workflow.
