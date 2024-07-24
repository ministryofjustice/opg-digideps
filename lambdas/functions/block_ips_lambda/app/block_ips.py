import os
import re
import time
from collections import defaultdict

import boto3
from datetime import datetime, timedelta

# Initialize boto3 clients

table_name = "BlockedIPs"
ip_set_name = "BlockedIPs"
ip_set_scope = "REGIONAL"


def query_cloudwatch_logs(log_group_name, log_stream_prefix):
    cloudwatch_logs = boto3.client("logs", region_name="eu-west-1")
    end_time = datetime.now()
    # We choose 7 mins so it overlaps the last run (which is every 5 mins)
    start_time = end_time - timedelta(minutes=7)
    start_timestamp = int(start_time.timestamp()) * 1000
    end_timestamp = int(end_time.timestamp()) * 1000

    response = cloudwatch_logs.start_query(
        logGroupName=log_group_name,
        startTime=start_timestamp,
        endTime=end_timestamp,
        queryString=f"""
      fields real_forwarded_for, request_uri, status
      | filter @logStream like "{log_stream_prefix}"
      | filter request_uri != "/health-check"
      | filter request_uri != "/login"
      | filter request_uri != "/"
      | sort @timestamp desc
      | limit 10000""",
    )

    # Get the query ID for fetching results
    query_id = response["queryId"]

    # Wait for the query to complete
    seconds_waiting = 0
    seconds_to_sleep = 1
    ten_minutes = 600
    while True:
        query_status = cloudwatch_logs.get_query_results(queryId=query_id)
        if query_status["status"] == "Complete" or seconds_waiting > ten_minutes:
            break
        time.sleep(seconds_to_sleep)
        seconds_waiting += seconds_to_sleep

    # Process each query result
    log_records = []
    request_uri = ""
    real_forwarded_for = ""
    status = ""
    for result_fields in query_status["results"]:
        for field_value in result_fields:
            if field_value["field"] == "real_forwarded_for":
                real_forwarded_for = field_value["value"]
            elif field_value["field"] == "request_uri":
                request_uri = field_value["value"]
            elif field_value["field"] == "status":
                status = field_value["value"]

        log_records.append(
            {
                "real_forwarded_for": f"{real_forwarded_for}/32",
                "request_uri": request_uri,
                "status": status,
            }
        )

    return log_records


def filter_logs(logs):
    filtered_logs = defaultdict(
        lambda: {
            "404_with_suffix": 0,
            "404_without_suffix": 0,
            "403_requests": 0,
            "2xx_or_3xx_not_root": 0,
        }
    )

    suffix_pattern = re.compile(r".*\.\w+$")

    for log in logs:
        ip = log["real_forwarded_for"]
        status = int(log["status"])
        request_uri = log["request_uri"]

        if status == 404:
            if suffix_pattern.match(request_uri):
                filtered_logs[ip]["404_with_suffix"] += 1
            else:
                filtered_logs[ip]["404_without_suffix"] += 1
        elif status == 403:
            filtered_logs[ip]["403_requests"] += 1
        elif status < 399 and request_uri != "/":
            filtered_logs[ip]["2xx_or_3xx_not_root"] += 1

    ips = []
    # We have slightly higher threshold for non suffixed in case they mistype a url
    # In both cases if they hit a valid endpoint in the time then we don't block them
    for ip, value in filtered_logs.items():
        if value["404_without_suffix"] > 5 and value["2xx_or_3xx_not_root"] < 1:
            ips.append(ip)
        elif value["404_with_suffix"] > 1 > value["2xx_or_3xx_not_root"]:
            ips.append(ip)

    return ips


def update_dynamodb_table(ips):
    dynamodb = boto3.client("dynamodb", region_name="eu-west-1")
    current_time = datetime.utcnow()
    timeout_expiry_short = current_time + timedelta(minutes=30)
    timeout_expiry_medium = current_time + timedelta(hours=4)
    timeout_expiry_long = current_time + timedelta(hours=12)
    ttl = current_time + timedelta(hours=12)

    for ip in ips:
        response = dynamodb.get_item(TableName=table_name, Key={"IP": {"S": ip}})
        if "Item" in response:
            row_updated_at = datetime.fromtimestamp(
                int(response["Item"]["UpdatedAt"]["N"])
            )
            # As we have overlapping time ranges and an IP would be blocked if it got here,
            # we discount records updated in last 10 minutes.
            ten_minutes_ago = current_time - timedelta(minutes=10)

            if row_updated_at > ten_minutes_ago:
                print(
                    "Found matching IP entry from less than 10 minutes ago. Not updating IP ranges."
                )
            else:
                if int(response["Item"]["BlockCounter"]["N"]) == 1:
                    timeout_expiry = timeout_expiry_medium
                else:
                    timeout_expiry = timeout_expiry_long
                dynamodb.update_item(
                    TableName=table_name,
                    Key={"IP": {"S": ip}},
                    UpdateExpression="SET BlockCounter = BlockCounter + :inc, TimeoutExpiry = :timeout, "
                    "ExpiresTTL = :ttl, "
                    "UpdatedAt = :now",
                    ExpressionAttributeValues={
                        ":inc": {"N": "1"},
                        ":timeout": {"N": str(int(timeout_expiry.timestamp()))},
                        ":now": {"N": str(int(current_time.timestamp()))},
                        ":ttl": {"N": str(int(ttl.timestamp()))},
                    },
                )
                print(f"Bumping IP {ip} to next lockout level.")
        else:
            dynamodb.put_item(
                TableName=table_name,
                Item={
                    "IP": {"S": ip},
                    "TimeoutExpiry": {"N": str(int(timeout_expiry_short.timestamp()))},
                    "BlockCounter": {"N": "1"},
                    "ExpiresTTL": {"N": str(int(ttl.timestamp()))},
                    "UpdatedAt": {"N": str(int(current_time.timestamp()))},
                },
            )


def get_blocked_ips():
    dynamodb = boto3.client("dynamodb", region_name="eu-west-1")
    response = dynamodb.scan(
        TableName=table_name, ProjectionExpression="IP, TimeoutExpiry"
    )
    current_time = datetime.utcnow()
    ips = []
    for item in response["Items"]:
        if int(item["TimeoutExpiry"]["N"]) - int(current_time.timestamp()) >= 0:
            ips.append(item["IP"]["S"])

    return ips


def update_waf_ip_set(ip_set_name, ip_set_scope, ips):
    waf = boto3.client("wafv2", region_name="eu-west-1")
    response = waf.list_ip_sets(Scope=ip_set_scope)
    ip_set_id = None

    for ip_set in response["IPSets"]:
        if ip_set["Name"] == ip_set_name:
            ip_set_id = ip_set["Id"]
            break

    if ip_set_id is None:
        raise Exception("IP set not found")

    # Get the current IP set
    ip_set = waf.get_ip_set(Name=ip_set_name, Scope=ip_set_scope, Id=ip_set_id)

    # Update the IP set
    response = waf.update_ip_set(
        Name=ip_set_name,
        Scope=ip_set_scope,
        Id=ip_set_id,
        Addresses=ips,
        LockToken=ip_set["LockToken"],
    )
    if response["ResponseMetadata"]["HTTPStatusCode"] == 200:
        print(f"Updated IP set {ip_set_name} correctly")
        response_object = {
            "statusCode": 200,
            "headers": {
                "Content-Type": "application/json",
                "Access-Control-Allow-Origin": "*",
            },
            "body": "IPs updated OK",
        }
    else:
        print(f"Error updating {ip_set_name}")
        response_object = {
            "statusCode": 500,
            "headers": {
                "Content-Type": "application/json",
                "Access-Control-Allow-Origin": "*",
            },
            "body": "Problem updating IPs",
        }
    return response_object


def lambda_handler(event, context):
    environment = os.getenv("ENVIRONMENT", "")
    log_group_name = environment
    log_stream_prefix = f"front.{environment}.web"
    logs = query_cloudwatch_logs(log_group_name, log_stream_prefix)
    filtered_ips = filter_logs(logs)
    print(f"New Malicious IPs identified: {filtered_ips}")
    update_dynamodb_table(filtered_ips)
    blocked_ips = get_blocked_ips()
    print(f"IPs to block according to dynamodb: {blocked_ips}")
    response = update_waf_ip_set(ip_set_name, ip_set_scope, blocked_ips)

    return response
