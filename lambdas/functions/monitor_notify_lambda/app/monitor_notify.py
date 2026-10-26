import json
import logging
import os

from message_generation.alarm import alarm_message
from message_generation.anomaly import anomaly_detection_message
from message_generation.cloudwatch import cloudwatch_message
from message_generation.cloudwatch_business import cloudwatch_business_message
from message_generation.github_actions import github_actions_message
from message_sending.send import send_message

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def valid_alarm_name(topic, subject):
    alarm_filters = {
        "custom_cloudwatch_alarms": [
            {
                "must_include": [
                    "ALARM:",
                    "breakglass console login check",
                    "ALARM:",
                    "data-access console login check",
                ],
            },
        ],
        "alerts": [
            {
                "must_include": ["ALARM:"],
            }
        ],
    }

    for alarm_type, rules in alarm_filters.items():
        if alarm_type in topic:
            for rule in rules:
                must = rule.get("must_include", [])

                if alarm_type == "custom_cloudwatch_alarms":
                    if ("ALARM:" in subject) and (
                        "breakglass console login check" in subject
                        or "data-access console login check" in subject
                    ):
                        return True
                elif all(keyword in subject for keyword in must):
                    return True

            return False

    return True


def generate_message(event):
    if "Records" in event:
        for record in event["Records"]:
            if "Sns" not in record:
                continue

            sns = record["Sns"]
            message = json.loads(sns["Message"])
            topic_arn = sns["TopicArn"]
            subject = sns.get("Subject", "")
            region = topic_arn.split(":")[3]

            if (
                "AlarmName" in message
                and message.get("NewStateValue") == "ALARM"
                and valid_alarm_name(topic_arn, subject)
            ):
                return alarm_message(message, region)

    elif "GithubActions" in event:
        return github_actions_message(event["GithubActions"])

    elif "scheduled-event-detail" in event:
        message = event["scheduled-event-detail"]

        if "business_functionality_" in message["job-name"]:
            return cloudwatch_business_message(message)

        if "anomaly_detection_" in message["job-name"]:
            return anomaly_detection_message(message)

        return cloudwatch_message(message)

    else:
        logger.warning("Unknown event. No actions performed")

    return ""


def lambda_handler(event, context):
    if os.getenv("PAUSE_NOTIFICATIONS", "0") == "1":
        return 0

    payload = generate_message(event)

    if not payload:
        return {
            "statusCode": 200,
            "body": "Event processed without a Slack notification",
        }

    return send_message(payload)
