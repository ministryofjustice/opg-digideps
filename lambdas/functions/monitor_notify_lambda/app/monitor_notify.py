import logging
import os
import json
from message_generation.alarm import alarm_message
from message_generation.github_actions import github_actions_message
from message_generation.cloudwatch_business import cloudwatch_business_message
from message_generation.cloudwatch import cloudwatch_message
from message_sending.send import send_message

# Create a logger instance
logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def generate_message(event):
    payload = ""
    if "Records" in event:
        for record in event["Records"]:
            if "Sns" in record:
                message = json.loads(record["Sns"]["Message"])
                region = record["Sns"]["TopicArn"].split(":")[3]
                if "AlarmName" in message:
                    payload = alarm_message(message, region)
    elif "GithubActions" in event:
        message = event["GithubActions"]
        payload = github_actions_message(message)
    elif "scheduled-event-detail" in event:
        message = event["scheduled-event-detail"]
        if "business_functionality_" in message["job-name"]:
            payload = cloudwatch_business_message(message)
        else:
            payload = cloudwatch_message(message)
    else:
        logger.warning("Unknown event. No actions performed")

    return payload


def lambda_handler(event, context):
    payload = generate_message(event)

    pause_notifications = os.getenv("PAUSE_NOTIFICATIONS", "0")
    if pause_notifications == "1":
        return 0

    response = send_message(payload)
    return response
