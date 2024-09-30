import logging
import urllib

logger = logging.getLogger(__name__)
logger.setLevel("INFO")


def get_service_url(region: str, service: str) -> str:
    return f"https://console.aws.amazon.com/{service}/home?region={region}"


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

    with open("templates/sns_alerts.txt", "r") as file:
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
