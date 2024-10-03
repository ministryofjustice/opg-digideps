import boto3
import requests
import logging
import re
import time
from datetime import datetime, timedelta
import json

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


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
    pattern = r"^\w+(?:_\w+)* - (success|failure) - .+$"
    # Use re.match to check if the string matches the pattern
    match = re.match(pattern, log_string)
    return bool(match)


def json_load_dicts(raw_dicts):
    dicts = []
    for raw_dict in raw_dicts:
        dictionary = json.loads(raw_dict)
        dicts.append(dictionary)
    return dicts


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
