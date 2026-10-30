import json
import logging
import os
import urllib.parse
from datetime import datetime, timedelta

logger = logging.getLogger(__name__)
logger.setLevel("INFO")

ALERT_CONFIGURATION_FILE = os.path.join(
    os.path.dirname(os.path.dirname(__file__)),
    "alerting",
    "alarms.json",
)

DEFAULT_LOGS = {
    "log_group": "{environment}",
    "query": [
        "fields @timestamp, @message, @logStream, @log",
        "| sort @timestamp desc",
        "| limit 100",
    ],
}


def get_alert_configuration(alarm_name):
    with open(ALERT_CONFIGURATION_FILE, encoding="utf-8") as file:
        configurations = json.load(file)

    for name, configuration in configurations.items():
        if alarm_name == name or alarm_name.endswith(f"-{name}"):
            return configuration

    return {}


def get_environment(alarm_name, configuration):
    configuration_name = configuration.get("name")

    if not configuration_name:
        return alarm_name.split("-", 1)[0]

    if alarm_name == configuration_name:
        return os.getenv("ENVIRONMENT", "unknown")

    return alarm_name.removesuffix(f"-{configuration_name}")


def encode_cloudwatch_value(value):
    return urllib.parse.quote(str(value), safe="").replace("%", "*")


def get_alarm_url(region, alarm_name):
    alarm_name = urllib.parse.quote(alarm_name, safe="")

    return (
        "https://console.aws.amazon.com/cloudwatch/home"
        f"?region={region}#alarm:alarmFilter=ANY;name={alarm_name}"
    )


def get_log_url(message, region, environment, logs):
    if not message.get("StateChangeTime"):
        return None

    alarm_time = datetime.fromisoformat(
        message["StateChangeTime"].replace("Z", "+00:00")
    )

    period = int(message.get("Trigger", {}).get("Period", 60))
    evaluation_periods = int(message.get("Trigger", {}).get("EvaluationPeriods", 5))

    start_time = alarm_time - timedelta(seconds=period * (evaluation_periods + 1))

    start = start_time.isoformat(timespec="milliseconds").replace("+00:00", "Z")
    end = alarm_time.isoformat(timespec="milliseconds").replace("+00:00", "Z")

    query = encode_cloudwatch_value("\n".join(logs["query"]))
    log_group = encode_cloudwatch_value(
        logs["log_group"].replace("{environment}", environment)
    )

    query_parameters = [
        f"end~'{encode_cloudwatch_value(end)}",
        f"start~'{encode_cloudwatch_value(start)}",
        "timeType~'ABSOLUTE",
        "tz~'UTC",
        f"editorString~'{query}",
        "isLiveTail~false",
        f"source~(~'{log_group})",
    ]

    query_details = "~(" + "~".join(query_parameters) + ")"

    route = urllib.parse.quote(
        f"?queryDetail={query_details}",
        safe="~()*'",
    ).replace("%", "$")

    return (
        f"https://{region}.console.aws.amazon.com/cloudwatch/home"
        f"?region={region}#logsV2:logs-insights{route}"
    )


def create_link(label, url):
    return f"<{url}|{label}>" if url else None


def alarm_message(message, region):
    alarm_name = message.get("AlarmName", "Unknown alarm")
    configuration = get_alert_configuration(alarm_name)
    environment = get_environment(alarm_name, configuration)

    title = configuration.get("title", alarm_name)
    description = configuration.get(
        "description",
        message.get(
            "AlarmDescription",
            "A CloudWatch alarm has changed state.",
        ),
    )

    log_url = get_log_url(
        message,
        region,
        environment,
        configuration.get("logs", DEFAULT_LOGS),
    )

    sections = [
        f":warning: *ALARM - {alarm_name}* :x:",
        f"*Alert:* {title}",
        f"*Environment:* {environment}",
        f"*Summary:* {description}",
    ]

    investigation_steps = configuration.get("investigation_steps", [])

    if investigation_steps:
        steps = "\n".join(
            f"{number}. {step}"
            for number, step in enumerate(investigation_steps, start=1)
        )
        sections.append(f"*What to do:*\n{steps}")

    links = [
        create_link("View matching logs", log_url),
        create_link(
            "View alarm",
            get_alarm_url(region, alarm_name),
        ),
        create_link(
            "Documentation",
            configuration.get("confluence_link"),
        ),
        create_link(
            "Terraform",
            configuration.get("terraform_link"),
        ),
    ]

    sections.append("*Links:* " + " | ".join(link for link in links if link))

    logger.info(f"Processed alarm {alarm_name}")

    return {
        "text": "\n\n".join(sections),
        "channel": "default",
    }
