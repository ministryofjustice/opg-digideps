import logging
import re
import urllib
from datetime import datetime, timedelta
import boto3
from collections import defaultdict
import requests
import json

# Create a logger instance
logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def get_slack_webhook_secret(secret_name):
    try:
        # Initialize the Secrets Manager client
        client = boto3.client("secretsmanager")
        response = client.get_secret_value(SecretId=secret_name)
        secret = response["SecretString"]
        return secret
    except Exception as e:
        # Handle any exceptions here
        logger.error(f"Error: {str(e)}")
        return None


def get_service_url(region: str, service: str) -> str:
    return f"https://console.aws.amazon.com/{service}/home?region={region}"


def parse_human_time_span(human_time_span):
    # Split the input by space to separate the quantity and unit
    parts = human_time_span.split()

    if len(parts) != 2:
        raise ValueError(
            "Invalid time span format. Use 'X unit', e.g., '1 hour' or '3 days'"
        )

    quantity, unit = int(parts[0]), parts[1].lower()

    # Define time units in seconds
    time_units = {
        "second": 1,
        "seconds": 1,
        "minute": 60,
        "minutes": 60,
        "hour": 3600,
        "hours": 3600,
        "day": 86400,
        "days": 86400,
    }

    if unit not in time_units:
        raise ValueError(
            "Invalid time unit. Use 'second(s)', 'minute(s)', 'hour(s)', or 'day(s)'"
        )

    # Calculate the start and end times
    end_time = datetime.now()
    start_time = end_time - timedelta(seconds=quantity * time_units[unit])

    # Convert to Unix timestamps
    start_timestamp = (
        int(start_time.timestamp()) * 1000
    )  # Multiplying by 1000 to get milliseconds
    end_timestamp = int(end_time.timestamp()) * 1000

    return {"start_time": start_timestamp, "end_time": end_timestamp}


def check_log_format(log_string):
    # Define a regular expression pattern for the desired format
    pattern = r"^\w+(?:_\w+)* - (success|failure) - .+$"
    # Use re.match to check if the string matches the pattern
    match = re.match(pattern, log_string)
    # Return True if it's a match, otherwise False
    return bool(match)


def search_log_group(log_group, log_entries, search_timespan):
    # Initialize AWS CloudWatch Logs client
    client = boto3.client("logs")

    # Create a dictionary to store counts of event_name - status combinations
    event_counts = defaultdict(int)
    descriptions = {}
    timespan = parse_human_time_span(search_timespan)

    # Iterate through each log entry pattern to search for
    for log_entry_pattern in log_entries:
        # Perform a CloudWatch Logs query for the log group
        response = client.start_query(
            logGroupName=log_group,
            startTime=int(timespan["start_time"]),
            endTime=int(timespan["end_time"]),
            queryString=f"""
                fields @message
                | filter @message like "{log_entry_pattern}"
                | sort @timestamp desc
                | limit 10000""",
        )

        # Get the query ID for fetching results
        query_id = response["queryId"]

        # Wait for the query to complete
        while True:
            query_status = client.get_query_results(queryId=query_id)
            if query_status["status"] == "Complete":
                break

        # Process each query result
        for result in query_status["results"]:
            for field in result:
                if field["field"] == "@message":
                    value = field["value"]
                    if check_log_format(value):
                        value_parts = value.split(" - ")
                        job_name = value_parts[0]
                        status = value_parts[1]
                        description = value_parts[2]
                        # Increment the count for this event_name - status combination
                        event_counts[f"{job_name}-{status}"] += 1
                        # just takes the last one
                        descriptions[f"{job_name}-{status}"] = description

    template_values_collection = []
    for key, count in event_counts.items():
        key_parts = key.split("-")
        template_values = {
            "log_title": key_parts[0],
            "count": count,
            "status": key_parts[1],
            "description": descriptions[key],
        }
        template_values_collection.append(template_values)

    return template_values_collection


def is_bank_holiday():
    # Get today's date in the format used in the JSON response
    today_date = datetime.now().date().isoformat()

    # Make a GET request to fetch the JSON data
    url = "https://www.gov.uk/bank-holidays.json"
    response = requests.get(url)

    if response.status_code == 200:
        data = response.json()
        england_and_wales_holidays = data.get("england-and-wales", [])

        # Check if today's date is in the list of bank holidays
        for holiday in england_and_wales_holidays.get("events", []):
            if holiday.get("date") == today_date:
                return True  # Today is a bank holiday
        return False  # Today is not a bank holiday
    else:
        logger.warning("Failed to fetch bank holiday data.")
        return False  # Unable to determine if today is a bank holiday


def cloudwatch_event(event):
    job_name = event["job-name"]
    logger.info(f"Attempting to process scheduled event for job {job_name}")

    log_group = event["log-group"]
    log_entries = event["log-entries"]
    search_timespan = event["search-timespan"]
    run_bank_holidays = event["bank-holidays"]

    bank_holiday_check = (
        is_bank_holiday() if run_bank_holidays.lower() == "true" else False
    )

    if bank_holiday_check:
        logger.info(
            "It's a bank holiday and bank holiday check is on. Skipping log check"
        )
        return ""
    template_values_collection = search_log_group(
        log_group, log_entries, search_timespan
    )
    status_emoji = ""
    if len(template_values_collection) == 0:
        success_string = "Failure"
        status_emoji = ":mario_wave_bye:"
        main_body = (
            f"The above job has not run during the last {search_timespan}.\n\n"
            "Please check what has gone wrong."
        )
    elif (
        len(template_values_collection) > 1
        or template_values_collection[0]["count"] > 1
    ):
        main_body = ""
        for template_value in template_values_collection:
            main_body = (
                f"{main_body}*{template_value['log_title']}* - *{template_value['status']}*: "
                f"{template_value['count']}\n"
                f"Description: {template_value['description']}\n\n"
            )
        success_string = "Results"
        status_emoji = ""
    elif len(template_values_collection) == 1:
        template_value = template_values_collection[0]
        status = template_value["status"]
        success_string = "Success" if status == "success" else "Failure"
        main_body = template_value["description"]
        status_emoji = ":white_check_mark:" if status == "success" else ":x:"

    with open("cloudwatch_event.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        job_name=job_name,
        success_string=success_string,
        status_emoji=status_emoji,
        main_body=main_body,
    )

    return formatted_text


def alarm_message(message, region):
    alarm_name = message.get("AlarmName")
    logger.info(f"Attempting to process alarm event for branch {alarm_name}")

    environment = alarm_name.split("-")[0] if "-" in alarm_name else "Unknown"
    new_state = message.get("NewStateValue")
    old_state = message.get("OldStateValue")
    service = "cloudwatch"
    service_url = get_service_url(region, service)
    alarm_url = (
        f"{service_url}#alarm:alarmFilter=ANY;name={urllib.parse.quote(alarm_name)}"
    )

    insights_log_url = f"https://{region}.console.aws.amazon.com/cloudwatch/home?region={region}#logsV2:logs"
    insights_period = f"-insights$3FqueryDetail$3D~(end~0~start~-3600~timeType~'RELATIVE~unit~'seconds~editorString~'"

    if "5xx" in alarm_name:
        fields = "fields*20*40timestamp*2c*20status*2c*20request_uri*2c*20*40message*0a*7c*20"
        filter = "filter*20status*20*3e*20499*0a*7c*20"
    elif "critical" in alarm_name:
        fields = "fields*20*40timestamp*2c*20*40message*0a*7c*20"
        filter = "filter*20*40message*20like*20*27CRITICAL*27*0a*7c*20"
    elif "PHPError" in alarm_name:
        fields = "fields*20*40timestamp*2c*20*40message*0a*7c*20"
        filter = (
            "filter*20*40message*20*3d*7e*20*2f*5c*5berror*5c*5d.*2a*7c*5c*5bcrit*5c*5d.*2a*7c*5c*5balert*5c*5d"
            ".*2a*7c*5c*5bemerg*5c*5d.*2a*2f*0a*7c*20"
        )
    else:
        fields = "fields*20*40timestamp*2c*20*40message*2c*20*40logStream*2c*20*40log*0a*7c*20"
        filter = ""

    sort = "sort*20*40timestamp*20desc*0a*7c*20limit*20100~"
    queryId = (
        "queryId~'a470863db7311d3b-1131594d-4a8e566-832b5bb8-cf15779ff535ffd2c4e411b~"
    )
    source = f"source~(~'{environment}))"

    log_url = (
        f"{insights_log_url}{insights_period}{fields}{filter}{sort}{queryId}{source}"
    )

    with open("sns_alerts.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        alarm_name=alarm_name,
        old_state=old_state,
        new_state=new_state,
        region=region,
        alarm_url=alarm_url,
        environment=environment,
        log_url=log_url,
    )

    return formatted_text


def github_actions_message(message):
    branch = message["Branch"]
    logger.info(f"Attempting to process github actions event for branch {branch}")

    workflow_name = message["WorkflowName"]
    github_actor = message["GhActor"]
    success = message["Success"]
    job_url = message["JobUrl"]
    frontend_url = message["FrontendUrl"]
    admin_url = message["AdminUrl"]
    commit_message = message["CommitMessage"]
    scheduled_task = message["ScheduledTask"]

    path_to_live = True if "Path to live" in workflow_name else False

    status_emoji = ":white_check_mark:" if success == "true" else ":x:"
    success_string = "Success" if success == "true" else "Failure"
    workflow_type = "Digideps Live Release" if path_to_live else "Digideps Workflow"
    extra_emoji = ":rocket:" if path_to_live and success == "true" else ""

    if scheduled_task != "":
        with open("github_actions_scheduled_task.txt", "r") as file:
            template_text = file.read()

        formatted_text = template_text.format(
            scheduled_task=scheduled_task,
            status_emoji=status_emoji,
            success_string=success_string,
            job_url=job_url,
        )
    else:
        with open("github_actions.txt", "r") as file:
            template_text = file.read()

        formatted_text = template_text.format(
            workflow_name=workflow_name,
            workflow_type=workflow_type,
            extra_emoji=extra_emoji,
            github_actor=github_actor,
            status_emoji=status_emoji,
            success_string=success_string,
            job_url=job_url,
            branch=branch,
            frontend_url=frontend_url,
            admin_url=admin_url,
            commit_message=commit_message,
        )

    return formatted_text


def generate_message(event):
    response = ""
    if "Records" in event:
        for record in event["Records"]:
            if "Sns" in record:
                message = json.loads(record["Sns"]["Message"])
                region = record["Sns"]["TopicArn"].split(":")[3]
                if "AlarmName" in message:
                    response = alarm_message(message, region)
    elif "GithubActions" in event:
        message = event["GithubActions"]
        response = github_actions_message(message)
    elif "scheduled-event-detail" in event:
        message = event["scheduled-event-detail"]
        response = cloudwatch_event(message)
    else:
        logger.warning("Unknown event. No actions performed")

    return response


def send_message(payload):
    # Data for the message
    data = {"text": payload}
    # Send the POST request to the Slack webhook URL
    webhook_url = get_slack_webhook_secret("slack-webhook-url")
    response = requests.post(webhook_url, data=json.dumps(data))
    # Check the response
    if response.status_code == 200:
        logger.info(f"Slack message sent")
    else:
        logger.warning(
            f"Failed to send slack message: {response.status_code} - {response.text}"
        )

    return response


def lambda_handler(event, context) -> str:
    payload = generate_message(event)
    response = send_message(payload)

    return response
