import boto3
import urllib.request
import urllib.error
import json
import logging

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


def send_message(payload):
    # Create a response object
    response_object = {
        "statusCode": None,
        "headers": {
            "Content-Type": "application/json",
            "Access-Control-Allow-Origin": "*",
        },
        "body": "",
    }
    if payload == "":
        response_object["statusCode"] = 400
        response_object["body"] = "Invalid request - Not a valid slack alert"
        return response_object
    # Data for the message
    data = {"text": payload["text"]}
    # Send the POST request to the Slack webhook URL
    webhook_url = get_slack_webhook_secret("slack-webhook-url", payload["channel"])
    req = urllib.request.Request(
        webhook_url,
        data=json.dumps(data).encode("utf-8"),
        headers={"Content-Type": "application/json"},
        method="POST",
    )

    try:
        with urllib.request.urlopen(req) as response:
            if response.status == 200:
                logger.info(f"Slack message sent")
                response_object["statusCode"] = 200
                response_object["body"] = "Event processed successfully"
            else:
                response_text = response.read().decode("utf-8")
                logger.warning(
                    f"Failed to send slack message: {response.status} - {response_text}"
                )
                response_object["statusCode"] = 500
                response_object["body"] = "Event failed to process"
    except urllib.error.HTTPError as e:
        response_text = e.read().decode("utf-8")
        logger.warning(f"Failed to send slack message: {e.code} - {response_text}")
        response_object["statusCode"] = 500
        response_object["body"] = "Event failed to process"
    except urllib.error.URLError as e:
        logger.warning(f"Failed to send slack message: {str(e)}")
        response_object["statusCode"] = 500
        response_object["body"] = "Event failed to process"

    return response_object
