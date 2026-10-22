import json
import logging
import urllib.parse
import uuid
from datetime import datetime, timedelta, timezone
from functools import lru_cache
from pathlib import Path

logger = logging.getLogger(__name__)
logger.setLevel("INFO")

DEFAULT_LOG_CONFIGURATION = {
    "log_group": "{environment}",
    "query": [
        "fields @timestamp, @message, @logStream, @log",
        "| sort @timestamp desc",
        "| limit 100",
    ],
}


def get_alert_configuration_directory():
    for directory in Path(__file__).resolve().parents:
        alert_directory = directory / "alerting" / "alarms"
        if alert_directory.is_dir():
            return alert_directory

    return Path(__file__).resolve().parent.parent / "alerting" / "alarms"


@lru_cache
def get_alert_configurations():
    configurations = {}
    duplicate_names = set()

    for configuration_file in get_alert_configuration_directory().rglob("*.json"):
        try:
            with configuration_file.open() as file:
                configuration = json.load(file)
        except (OSError, json.JSONDecodeError) as error:
            logger.error(f"Unable to load {configuration_file}: {error}")
            continue

        name = configuration.get("name")
        if not name:
            logger.error(f"Alert configuration {configuration_file} has no name")
            continue

        if name in duplicate_names:
            continue

        if name in configurations:
            logger.error(f"More than one alert configuration uses the name {name}")
            configurations.pop(name)
            duplicate_names.add(name)
            continue

        configurations[name] = configuration

    return configurations


def get_environment(alarm_name):
    return alarm_name.split("-", 1)[0] if "-" in alarm_name else "unknown"


def get_configuration(alarm_name, environment):
    configuration_name = alarm_name.removeprefix(f"{environment}-")
    return get_alert_configurations().get(configuration_name, {})


def get_alarm_url(region, alarm_name):
    encoded_alarm_name = urllib.parse.quote(alarm_name)
    return (
        "https://console.aws.amazon.com/cloudwatch/home"
        f"?region={region}#alarm:alarmFilter=ANY;name={encoded_alarm_name}"
    )


def encode_cloudwatch_value(value):
    return urllib.parse.quote(value, safe="").replace("%", "*")


def encode_cloudwatch_route(value):
    return urllib.parse.quote(value, safe="~()*'").replace("%", "$")


def parse_state_change_time(value):
    return datetime.fromisoformat(value.replace("Z", "+00:00")).astimezone(timezone.utc)


def format_timestamp(value):
    if not value:
        return None

    return (
        parse_state_change_time(value)
        .isoformat(timespec="milliseconds")
        .replace("+00:00", "Z")
    )


def get_log_url(message, region, environment, configuration):
    logs = configuration.get("logs")
    state_change_time = message.get("StateChangeTime")

    if not logs or not state_change_time:
        return None

    trigger = message.get("Trigger", {})
    period = int(trigger.get("Period", 60))
    evaluation_periods = int(trigger.get("EvaluationPeriods", 5))
    end_time = parse_state_change_time(state_change_time)
    start_time = end_time - timedelta(seconds=period * (evaluation_periods + 1))
    query = "\n".join(logs["query"])
    log_group = logs["log_group"].replace("{environment}", environment)

    query_details = (
        "~(end~'"
        + encode_cloudwatch_value(
            end_time.isoformat(timespec="milliseconds").replace("+00:00", "Z")
        )
        + "~start~'"
        + encode_cloudwatch_value(
            start_time.isoformat(timespec="milliseconds").replace("+00:00", "Z")
        )
        + "~timeType~'ABSOLUTE~tz~'UTC~editorString~'"
        + encode_cloudwatch_value(query)
        + "~isLiveTail~false~queryId~'"
        + str(uuid.uuid4())
        + "~source~(~'"
        + encode_cloudwatch_value(log_group)
        + "))"
    )
    route = encode_cloudwatch_route(f"?queryDetail={query_details}")

    return (
        f"https://{region}.console.aws.amazon.com/cloudwatch/home"
        f"?region={region}#logsV2:logs-insights{route}"
    )


def alarm_details(message, region):
    alarm_name = message.get("AlarmName", "Unknown alarm")
    environment = get_environment(alarm_name)
    configuration = get_configuration(alarm_name, environment)
    log_configuration = configuration or {"logs": DEFAULT_LOG_CONFIGURATION}

    return {
        "alarm_name": alarm_name,
        "environment": environment,
        "title": configuration.get("title", alarm_name),
        "description": configuration.get(
            "description",
            message.get("AlarmDescription", "A CloudWatch alarm has changed state."),
        ),
        "severity": configuration.get("severity", "error"),
        "investigation_steps": configuration.get("investigation_steps", []),
        "alarm_url": get_alarm_url(region, alarm_name),
        "log_url": get_log_url(message, region, environment, log_configuration),
        "confluence_link": configuration.get("confluence_link"),
        "terraform_link": configuration.get("terraform_link"),
        "dedup_key": message.get("AlarmArn", f"{region}:{alarm_name}"),
        "state_change_time": format_timestamp(message.get("StateChangeTime")),
    }


def slack_link(name, url):
    return f"<{url}|{name}>" if url else None


def alarm_message(message, region):
    details = alarm_details(message, region)
    logger.info(f"Attempting to process alarm event {details['alarm_name']}")

    sections = [
        f":warning: *ALARM - {details['title']}* :x:",
        f"*Environment:* {details['environment']}",
        f"*Summary:* {details['description']}",
    ]

    if details["investigation_steps"]:
        steps = "\n".join(
            f"{number}. {step}"
            for number, step in enumerate(details["investigation_steps"], start=1)
        )
        sections.append(f"*What to do:*\n{steps}")

    links = [
        slack_link("View matching logs", details["log_url"]),
        slack_link("View alarm", details["alarm_url"]),
        slack_link("Documentation", details["confluence_link"]),
        slack_link("Terraform", details["terraform_link"]),
    ]
    sections.append("*Links:* " + " | ".join(link for link in links if link))

    return {"text": "\n\n".join(sections), "channel": "default"}
