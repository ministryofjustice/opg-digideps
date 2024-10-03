## Monitor Notify Lambda

The 'Monitor & Notify' lambda lives in our shared environment.

We have one lambda per account that is triggered in a variety of ways:

- Two SNS queues (one in eu-west-1 and one for the loadbalancers in us-east-1). When triggered in this way, it is responding to
alarms on our system. AWS metric alarms send details of the alert to the relevant SNS queue and our lambda formats the
alarm and provides additional context and sends it to the relevant slack channel.

- Cloudwatch event rules. There are two types of rules that are interpreted by the lambda (business rules and cloudwatch checks).
In both cases the process looks through the logs over a particular time period for a specific term or selection of terms.

The idea behind the checks is that we search the logs over a certain timeframe to find out whether a particular event has happened
and if it did, then was it a success or a failure. We then format the response into a message and notify slack.

For your cloudwatch event rule to be effective you need to supply the detail key
with the following items in json format:

```
log-group - the name of the log group to search (can be added from terraform)
log-entries - list of terms to search for (log records search need to be in format: title - success/failure - description
search-timespan - values are human readable like '1 hour' or '30 minutes'
bank-holidays - string of True of False if you want it to run on bank holidays or not
```

- Wrapper ci_monitor_notify which allows it to be called from the github actions workflow for action based notifications.
This is used within our pipelines to notify us of things happening in our pipelines (such as success or failure of a pipeline).
This doesn't technically call out to the lambda but it uses a lot of the shared code around templating and sending notifications so is
included as part of the lambda code.
