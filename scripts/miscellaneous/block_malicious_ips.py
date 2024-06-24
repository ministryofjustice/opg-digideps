import os
import re
import time
from collections import defaultdict

import boto3
from datetime import datetime, timedelta
import json

# Initialize boto3 clients
cloudwatch_logs = boto3.client("logs")
dynamodb = boto3.client("dynamodb")
waf = boto3.client("wafv2")
table_name = "BlockedIPs"
ip_set_name = "BlockedIPs"
ip_set_scope = "REGIONAL"  # Use 'CLOUDFRONT' for CloudFront IP sets


def query_cloudwatch_logs(log_group_name, log_stream_prefix):
    end_time = int(datetime.utcnow().timestamp() * 1000)
    start_time = int((datetime.utcnow() - timedelta(minutes=5)).timestamp() * 1000)

    response = cloudwatch_logs.start_query(
        logGroupName=log_group_name,
        startTime=start_time,
        endTime=end_time,
        queryString=f"""
      fields real_forwarded_for, request_uri, status
      | filter @logStream like "{log_stream_prefix}"
      | sort @timestamp desc
      | limit 10000""",
    )

    # Get the query ID for fetching results
    query_id = response["queryId"]

    # Wait for the query to complete
    while True:
        query_status = cloudwatch_logs.get_query_results(queryId=query_id)
        if query_status["status"] == "Complete":
            break
        time.sleep(1)

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
                "real_forwarded_for": real_forwarded_for,
                "request_uri": request_uri,
                "status": status,
            }
        )

    print(log_records)
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

    return filtered_logs


def update_dynamodb_table(logs):
    current_time = datetime.utcnow()
    timeout_expiry = current_time + timedelta(minutes=10)
    ttl = current_time + timedelta(hours=12)

    for log in logs:
        ip = log["real_forwarded_for"]
        response = dynamodb.get_item(TableName=table_name, Key={"IP": {"S": ip}})

        if "Item" in response:
            dynamodb.update_item(
                TableName=table_name,
                Key={"IP": {"S": ip}},
                UpdateExpression="SET BlockCounter = BlockCounter + :inc, TimeoutExpiry = :timeout, TTL = :ttl",
                ExpressionAttributeValues={
                    ":inc": {"N": "1"},
                    ":timeout": {"S": timeout_expiry.isoformat()},
                    ":ttl": {"N": str(int(ttl.timestamp()))},
                },
            )
        else:
            dynamodb.put_item(
                TableName=table_name,
                Item={
                    "IP": {"S": ip},
                    "TimeoutExpiry": {"S": timeout_expiry.isoformat()},
                    "BlockCounter": {"N": "1"},
                    "TTL": {"N": str(int(ttl.timestamp()))},
                },
            )


def get_blocked_ips():
    response = dynamodb.scan(TableName=table_name, ProjectionExpression="IP")

    ips = [item["IP"]["S"] for item in response["Items"]]
    return ips


def update_waf_ip_set(ip_set_name, ip_set_scope, ips):
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
    waf.update_ip_set(
        Name=ip_set_name,
        Scope=ip_set_scope,
        Id=ip_set_id,
        Addresses=ips,
        LockToken=ip_set["LockToken"],
    )


def main():
    environment = os.getenv("ENVIRONMENT", "")
    log_group_name = environment
    log_stream_prefix = f"front.{environment}.web"
    logs = query_cloudwatch_logs(log_group_name, log_stream_prefix)
    filtered_logs = filter_logs(logs)
    print(filtered_logs)
    for ip, value in filtered_logs.items():
        print(ip)
        print(value["2xx_or_3xx_not_root"])
    # update_dynamodb_table(filtered_logs)
    # blocked_ips = get_blocked_ips()
    # update_waf_ip_set(ip_set_name, ip_set_scope, blocked_ips)


if __name__ == "__main__":
    main()
