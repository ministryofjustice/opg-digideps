# Custom Alerting

This folder contains JSON configuration files used to produce clearer and more informative Slack notifications for our CloudWatch alarms. These are not the CloudWatch alarms themselves. Instead, they sit above that layer so that when we receive alerts, they are more informative and useful.

Each JSON file represents a CloudWatch alarm that can be configured. Alarms are grouped into subfolders based on the layer they relate to, such as the application or data layer.

## Folder Structure

- `alarms/application`: Application errors, PHP errors for example.
- `alarms/availability`: Endpoint and service availability alarms, Network Related.
- `alarms/database`: Database availability, capacity and performance alarms.
- `alarms/infrastructure`: ECS, Lambda, networking and other AWS infrastructure alarms.
- `alarms/security`: Authentication failures, suspicious activity and other generic security alarms.

## Alarm matching

The filenames of the alerts do not matter, its only for organising the number of alarms we have. Instead  the `name` property must match the CloudWatch alarm name without its environment prefix.

For example:

```text
production-critical-php-errors
```

uses the JSON configuration containing:

```json
"name": "critical-php-errors"
```

## Configuration fields

Each alert configuration contains:

- `name`: The CloudWatch alarm name without its environment prefix (see above)
- `title`: A clear title displayed in Slack.
- `description`: A short explanation of what the alarm means.
- `investigation_steps`: The initial actions someone should take when investigating the alarm.
- `logs`: The configuration used to create a link to the relevant CloudWatch logs, see examples, there is no way to link the actual log that caused the alarm.
- `confluence_link`: A link to the alarm documentation in Confluence.
- `terraform_link`: A link to the Terraform definition for the CloudWatch alarm.

## CloudWatch log links

Log messages are not copied into Slack or PagerDuty because they could contain sensitive information. There is also no good way of getting the exact log that caused the alarm.

Instead, the notification contains a link to CloudWatch Logs that tries to guess where it could be. The link uses:

- The log group configured in the JSON file.
- The Logs Insights query configured in the JSON file.
- The time when the alarm changed state.
- The alarm period and number of evaluation periods.

The link uses the time range of when the alarm was triggered, this means that opening an older Slack or PagerDuty notification should still show the logs from when the alarm triggered.

The time range covers the alarms complete evaluation window with some space added. For example, an alarm with a period of 60 seconds and five evaluation periods will produce a log link covering the six minutes before the alarm changed state.

## Simple steps for Adding a new alert configuration

1. Choose the appropriate directory under `alarms/` where you think the alarm should sit.
2. Create a JSON file with a descriptive filename (it wont matter what you call it)
3. Set `name` to the CloudWatch alarm name without its environment prefix. (See above)
4. Add a clear title and description that will be used by slack and pagerduty.
5. Add in some steps that will help guide people looking into the alert.
6. Add a Logs Insights query if needed, check examples if stuck.
7. Add the Confluence documentation link if there is one.
8. Add a link to the Terraform definition.
