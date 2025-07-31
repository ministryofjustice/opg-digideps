import os
import boto3
import logging
from datetime import datetime, timedelta, timezone
from collections import defaultdict

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def anomaly_detection_message(event):
    channel_identifier = event["channel-identifier"]
    anomaly_detections = get_log_anomalies_detected()
    if len(anomaly_detections) == 0:
        return ""
    severity_counts = get_severity_counts(anomaly_detections)
    details = get_anomalies_by_pattern_string(anomaly_detections)
    environment = os.environ.get("ENVIRONMENT", "environment not set")

    with open("templates/anomaly_detection.txt", "r") as file:
        template_text = file.read()

    formatted_text = template_text.format(
        count_medium=severity_counts["MEDIUM"],
        count_high=severity_counts["HIGH"],
        details=details,
        environment=environment,
    )

    payload = {"text": formatted_text, "channel": channel_identifier}

    return payload


def get_severity_counts(anomaly_detections):
    """
    Count how many anomalies exist for each severity level (priority).
    """
    counts = defaultdict(int)

    for anomaly in anomaly_detections:
        severity = anomaly.get("priority")
        if severity:
            counts[severity] += 1

    return dict(counts)


def get_anomalies_by_pattern_string(anomaly_detections):
    """
    Returns a string block of anomaly details, grouped by patternString,
    with 2 newlines between each group.
    """
    blocks = []

    for anomaly in anomaly_detections:
        pattern = anomaly.get("patternString", "<no pattern>")

        block = f"Pattern: {pattern}\n\n"
        blocks.append(block)

    return "\n\n".join(blocks)


def get_log_anomalies_detected():
    client = boto3.client("logs", region_name="eu-west-1")
    response = client.list_anomalies()
    anomalies = response.get("anomalies", [])
    cutoff_time = datetime.now(timezone.utc) - timedelta(hours=24)
    anomaly_detections = []

    for anomaly in anomalies:
        first_seen_epoch = anomaly.get("firstSeen")
        priority = anomaly.get("priority")

        # Skip LOW priority anomalies
        if priority == "LOW":
            continue

        try:
            # Convert epoch milliseconds to datetime
            first_seen_dt = datetime.fromtimestamp(
                first_seen_epoch / 1000, tz=timezone.utc
            )
        except Exception as e:
            print(f"Error parsing timestamp: {e}")
            continue

        # Skip anomalies not seen in the last 24 hours
        if first_seen_dt < cutoff_time:
            continue

        anomaly_detection = {
            "patternString": anomaly.get("patternString"),
            "priority": priority,
        }

        anomaly_detections.append(anomaly_detection)

    return anomaly_detections
