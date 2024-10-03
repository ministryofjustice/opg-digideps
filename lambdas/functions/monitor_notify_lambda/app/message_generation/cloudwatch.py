import boto3
import logging
import time
from collections import defaultdict
from .shared import is_bank_holiday, parse_human_time_span, check_log_format

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def cloudwatch_message(event):
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
    with open("templates/cloudwatch_event.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        job_name=job_name,
        success_string=success_string,
        status_emoji=status_emoji,
        main_body=main_body,
    )

    payload = {"text": formatted_text, "channel": channel_identifier}

    return payload


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
