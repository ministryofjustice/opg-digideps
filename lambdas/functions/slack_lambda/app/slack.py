import logging
import os
import re
import time
import urllib
from datetime import datetime, timedelta
import boto3
from collections import defaultdict
import requests
import json

# Create a logger instance
logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def get_slack_webhook_secret(secret_name, channel_identifier):
    try:
        # Initialize the Secrets Manager client
        client = boto3.client("secretsmanager")
        response = client.get_secret_value(SecretId=secret_name)
        secret = response["SecretString"]
        secret_dict = json.loads(secret)
        webhook_url = secret_dict.get(channel_identifier)
        return webhook_url
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


def log_event_search_by_log_entries(log_group, log_entries, search_timespan):
    # Initialize AWS CloudWatch Logs client
    client = boto3.client("logs")

    # Create a dictionary to store counts of event_name - status combinations
    event_counts = defaultdict(int)
    descriptions = {}
    timespan = parse_human_time_span(search_timespan)

    # Iterate through each log entry pattern to search for
    for log_entry_pattern in log_entries:
        logger.info(f"trying to find this pattern: {log_entry_pattern}")
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
            time.sleep(1)

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


def build_search_condition(search_term, method):
    if "*" in search_term:
        search_term_parts = search_term.split("/")
        final_search_term = next(
            part for part in search_term_parts if part != "*" and len(part) > 1
        )
        return (
            f"(request_uri like '/{final_search_term}' and request_method = '{method}')"
        )
    else:
        return f"(request_uri = '{search_term}' and request_method = '{method}')"


def create_query_string(search_terms_and_methods):
    query = """
        fields request_uri
        | filter request_uri like 'dummy-non-existent-value'"""

    for search_terms_and_method in search_terms_and_methods:
        search_term_string = search_terms_and_method["search_term"]
        method = search_terms_and_method["method"]
        search_terms = search_term_string.split("|")
        for search_term in search_terms:
            query += f"\nor {build_search_condition(search_term, method)}"

    return query


def get_terms_and_methods(log_entries):
    term_and_method_pairs = []
    for log_entry in log_entries:
        term_and_method_pairs.append(
            {
                "search_term": log_entry["search1"],
                "method": log_entry["method1"],
                "name": log_entry["name"],
                "search_elem": 1,
            }
        )
        term_and_method_pairs.append(
            {
                "search_term": log_entry["search2"],
                "method": log_entry["method2"],
                "name": log_entry["name"],
                "search_elem": 2,
            }
        )
    return term_and_method_pairs


def run_log_insights_query(query, log_group, timespan):
    client = boto3.client("logs")
    response = client.start_query(
        logGroupName=log_group,
        startTime=int(timespan["start_time"]),
        endTime=int(timespan["end_time"]),
        queryString=query,
    )
    # Get the query ID for fetching results
    query_id = response["queryId"]
    # Wait for the query to complete
    while True:
        query_status = client.get_query_results(queryId=query_id)
        if query_status["status"] == "Complete":
            break
        time.sleep(1)

    return query_status["results"]


def json_load_dicts(raw_dicts):
    dicts = []
    for raw_dict in raw_dicts:
        dictionary = json.loads(raw_dict)
        dicts.append(dictionary)
    return dicts


def get_uri_list_from_results(results):
    uris = []
    for result in results:
        for result_part in result:
            if result_part["field"] == "request_uri":
                uris.append(result_part["value"])
    return uris


def get_uri_counts(uri_list, search_terms_and_methods):
    uri_counts = {}
    for uri in uri_list:
        for search_term_and_method in search_terms_and_methods:
            search_term = search_term_and_method["search_term"]
            terms = search_term.split("|")
            for term in terms:
                base_term = term.replace("/*", "")
                if base_term in uri:
                    uri_counts[search_term] = uri_counts.get(search_term, 0) + 1

    return uri_counts


def create_assertions(assertion_dicts, uri_counts):
    assertions = []
    for assertion_dict in assertion_dicts:
        assertion = {
            "name": assertion_dict["name"],
            "search1_count": int(uri_counts.get(assertion_dict["search1"], 0)),
            "search2_count": int(uri_counts.get(assertion_dict["search2"], 0)),
            "total_count": 0,
            "threshold_pct": int(assertion_dict["percentage_threshold"]),
            "threshold_count": int(assertion_dict["count_threshold"]),
            "passed": True,
        }
        assertion["total_count"] = (
            assertion["search1_count"] + assertion["search2_count"]
        )
        threshold_pct = assertion["threshold_pct"]

        if threshold_pct != 0:
            threshold_pct_result = assertion["search1_count"] * (threshold_pct / 100)
        else:
            threshold_pct_result = 0

        percent_threshold_met = (
            True if assertion["search2_count"] > threshold_pct_result else False
        )
        count_threshold_met = (
            True if assertion["total_count"] >= assertion["threshold_count"] else False
        )

        if not percent_threshold_met and count_threshold_met:
            assertion["passed"] = False
        else:
            assertion["passed"] = True

        assertions.append(assertion)
        logger.info(assertion)

    return assertions


def create_payload(assertions, channel):
    failed_assertions = []
    main_body = ""
    for assertion in assertions:
        if not assertion["passed"]:
            failed_assertions.append(assertion)
            main_body = f"{main_body}\n\n{assertion}"

    with open("cloudwatch_business_failure.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        main_body=main_body,
    )

    if len(failed_assertions) > 0:
        payload = {"text": formatted_text, "channel": channel}
    else:
        print("Business issues check complete. No issues found. Exiting ...")
        exit(0)

    return payload


def cloudwatch_business_event(event):
    job_name = event["job-name"]
    logger.info(f"Attempting to process scheduled event for job {job_name}")

    log_group = event["log-group"]
    assertion_dicts_raw = event["log-entries"]
    search_timespan = event["search-timespan"]
    channel_identifier_failure = event["channel-identifier-failure"]

    timespan = parse_human_time_span(search_timespan)
    assertion_dicts = json_load_dicts(assertion_dicts_raw)
    search_terms_and_methods = get_terms_and_methods(assertion_dicts)
    query = create_query_string(search_terms_and_methods)
    logger.info(query)
    results = run_log_insights_query(query, log_group, timespan)
    uri_list = get_uri_list_from_results(results)
    uri_counts = get_uri_counts(uri_list, search_terms_and_methods)
    assertions = create_assertions(assertion_dicts, uri_counts)
    payload = create_payload(assertions, channel_identifier_failure)

    return payload


def cloudwatch_event(event):
    job_name = event["job-name"]
    logger.info(f"Attempting to process scheduled event for job {job_name}")

    log_group = event["log-group"]
    log_entries = event["log-entries"]
    search_timespan = event["search-timespan"]
    run_bank_holidays = event["bank-holidays"]
    channel_identifier_absent = event["channel-identifier-absent"]
    channel_identifier_success = event["channel-identifier-success"]
    channel_identifier_failure = event["channel-identifier-failure"]

    bank_holiday_check = (
        is_bank_holiday() if run_bank_holidays.lower() == "true" else False
    )

    logger.info(f"Running bank holiday check")
    if bank_holiday_check:
        logger.info(
            "It's a bank holiday and bank holiday check is on. Skipping log check"
        )
        return ""
    logger.info(f"Starting log search for {job_name}")
    template_values_collection = log_event_search_by_log_entries(
        log_group, log_entries, search_timespan
    )
    status_emoji = ""
    if len(template_values_collection) == 0:
        logger.info(f"No records found during the last {search_timespan}")
        success_string = "Failure"
        status_emoji = ":mario_wave_bye:"
        channel_identifier = channel_identifier_absent
        main_body = (
            f"The above job has not run during the last {search_timespan}.\n\n"
            "Please check what has gone wrong."
        )
    elif (
        len(template_values_collection) > 1
        or template_values_collection[0]["count"] > 1
    ):
        main_body = ""
        failed_events_exist = False
        for template_value in template_values_collection:
            main_body = (
                f"{main_body}*{template_value['log_title']}* - *{template_value['status']}*: "
                f"{template_value['count']}\n"
                f"Description: {template_value['description']}\n\n"
            )
            if template_value["status"].lower() == "failure":
                failed_events_exist = True
        success_string = "Results"
        status_emoji = ""
        channel_identifier = (
            channel_identifier_failure
            if failed_events_exist
            else channel_identifier_success
        )
    elif len(template_values_collection) == 1:
        template_value = template_values_collection[0]
        status = template_value["status"]
        success_string = "Success" if status == "success" else "Failure"
        main_body = template_value["description"]
        status_emoji = ":white_check_mark:" if status == "success" else ":x:"
        channel_identifier = (
            channel_identifier_success
            if status == "success"
            else channel_identifier_failure
        )
    with open("cloudwatch_event.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        job_name=job_name,
        success_string=success_string,
        status_emoji=status_emoji,
        main_body=main_body,
    )

    payload = {"text": formatted_text, "channel": channel_identifier}

    return payload


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

    payload = {"text": formatted_text, "channel": "default"}

    return payload


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

    status_emoji = ":white_check_mark:" if success == "yes" else ":x:"
    success_string = "Success" if success == "yes" else "Failure"
    workflow_type = "Digideps Live Release" if path_to_live else "Digideps Workflow"
    extra_emoji = ":rocket:" if path_to_live and success == "yes" else ""

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

    payload = {"text": formatted_text, "channel": "default"}

    return payload


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
            payload = cloudwatch_business_event(message)
        else:
            payload = cloudwatch_event(message)
    else:
        logger.warning("Unknown event. No actions performed")

    return payload


def send_message(payload):
    # Data for the message
    data = {"text": payload["text"]}
    # Send the POST request to the Slack webhook URL
    webhook_url = get_slack_webhook_secret("slack-webhook-url", payload["channel"])
    response = requests.post(webhook_url, data=json.dumps(data))
    # Check the response
    response_object = {
        "statusCode": None,
        "headers": {
            "Content-Type": "application/json",
            "Access-Control-Allow-Origin": "*",
        },
        "body": "",
    }
    if response.status_code == 200:
        logger.info(f"Slack message sent")
        response_object["statusCode"] = 200
        response_object["body"] = "Event processed successfully"
    else:
        logger.warning(
            f"Failed to send slack message: {response.status_code} - {response.text}"
        )
        response_object["statusCode"] = 500
        response_object["body"] = "Event failed to process"

    return response_object


def lambda_handler(event, context):
    payload = generate_message(event)

    pause_notifications = os.getenv("PAUSE_NOTIFICATIONS", "0")
    if pause_notifications == "1":
        return 0

    response = send_message(payload)
    return response
