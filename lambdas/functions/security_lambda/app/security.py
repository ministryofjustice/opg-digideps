from __future__ import annotations
import os
import re
import time
from collections import defaultdict
from dataclasses import dataclass
from typing import Final, Any
from fnmatch import fnmatch

import boto3
from datetime import datetime, timedelta, UTC

# Initialize boto3 clients

table_name = "BlockedIPs"
ip_set_name = "BlockedIPs"
ip_set_scope = "REGIONAL"

QUERY_LOOKBACK_MINUTES: Final[int] = 7
QUERY_TIMEOUT_SECONDS: Final[int] = 600
QUERY_POLL_INTERVAL_SECONDS: Final[int] = 1


@dataclass(slots=True)
class IpActivitySummary:
    not_found_with_suffix: int = 0
    not_found_without_suffix: int = 0
    forbidden_requests: int = 0
    successful_requests: int = 0
    authenticated_requests: int = 0

    @property
    def is_suspicious(self) -> bool:
        """Indicates probing/scanning behaviour."""

        return self.not_found_without_suffix > 5 or (
            self.not_found_with_suffix >= 1 and self.successful_requests == 0
        )

    @property
    def has_authenticated_activity(self) -> bool:
        """Indicates the IP accessed authenticated pages."""

        return self.authenticated_requests > 0


@dataclass(frozen=True, slots=True)
class LogRecord:
    real_forwarded_for: str
    request_uri: str
    status: int


# ==================== RETURN RESULTS LOGIC ====================
def query_cloudwatch_logs(
    log_group_name: str,
    log_stream_prefixes: list[str],
) -> list[LogRecord]:
    """Query recent nginx logs from CloudWatch Logs Insights."""

    client = boto3.client("logs", region_name="eu-west-1")

    start_time_ms, end_time_ms = get_query_time_range()

    query_id = start_logs_query(
        client=client,
        log_group_name=log_group_name,
        log_stream_prefixes=log_stream_prefixes,
        start_time_ms=start_time_ms,
        end_time_ms=end_time_ms,
    )

    results = wait_for_query_results(
        client=client,
        query_id=query_id,
    )

    return parse_log_records(results)


def get_query_time_range() -> tuple[int, int]:
    """Return the query start and end times in milliseconds."""

    end_time = datetime.now(UTC)
    start_time = end_time - timedelta(minutes=QUERY_LOOKBACK_MINUTES)

    return (
        int(start_time.timestamp() * 1000),
        int(end_time.timestamp() * 1000),
    )


def start_logs_query(
    client,
    log_group_name: str,
    log_stream_prefixes: list[str],
    start_time_ms: int,
    end_time_ms: int,
) -> str:
    """Start a CloudWatch Logs Insights query."""

    log_stream_filter = " or ".join(
        f'@logStream like "{prefix}"' for prefix in log_stream_prefixes
    )

    response = client.start_query(
        logGroupName=log_group_name,
        startTime=start_time_ms,
        endTime=end_time_ms,
        queryString=f"""
            fields real_forwarded_for, request_uri, status
            | filter ({log_stream_filter})
            | filter request_uri not in ["/health-check", "/login", "/"]
            | filter status > 0
            | sort @timestamp desc
            | limit 10000
        """,
    )

    return response["queryId"]


def wait_for_query_results(
    client,
    query_id: str,
) -> list[list[dict[str, str]]]:
    """Wait for a query to complete and return the results."""

    waited_seconds = 0

    while waited_seconds < QUERY_TIMEOUT_SECONDS:
        response = client.get_query_results(queryId=query_id)

        if response["status"] == "Complete":
            return response["results"]

        time.sleep(QUERY_POLL_INTERVAL_SECONDS)
        waited_seconds += QUERY_POLL_INTERVAL_SECONDS

    raise TimeoutError(
        f"CloudWatch query '{query_id}' timed out after "
        f"{QUERY_TIMEOUT_SECONDS} seconds"
    )


def parse_log_records(
    results: list[list[dict[str, str]]],
) -> list[LogRecord]:
    """Convert CloudWatch query results into LogRecord objects."""

    records: list[LogRecord] = []

    for result in results:
        fields = {
            field["field"]: field["value"]
            for field in result
            if "field" in field and "value" in field
        }

        records.append(
            LogRecord(
                real_forwarded_for=f'{fields.get("real_forwarded_for", "")}/32',
                request_uri=fields.get("request_uri", ""),
                status=int(fields.get("status", "0")),
            )
        )

    return records


# ==================== GET LOG TYPE COUNTS LOGIC ====================

PATH_PATTERNS_REQUIRING_LOGIN: Final[list[str]] = [
    "/report/*",
    "/admin/*",
    "/org/*",
]

FILE_SUFFIX_PATTERN: Final[re.Pattern[str]] = re.compile(r".*\.\w+$")


def summarise_log_records(
    records: list[LogRecord],
) -> dict[str, IpActivitySummary]:
    """Group request counts by source IP."""

    summaries: defaultdict[str, IpActivitySummary] = defaultdict(IpActivitySummary)

    for record in records:
        if should_ignore_request(record.request_uri):
            continue

        summary = summaries[record.real_forwarded_for]

        if record.status == 404:
            increment_not_found_count(
                summary=summary,
                request_uri=record.request_uri,
            )
            continue

        if record.status == 403:
            summary.forbidden_requests += 1
            continue

        if is_successful_request(
            status=record.status,
            request_uri=record.request_uri,
        ):
            summary.successful_requests += 1

            if is_authenticated_route(record.request_uri):
                summary.authenticated_requests += 1

    return dict(summaries)


def should_ignore_request(request_uri: str) -> bool:
    """Exclude well-known paths from analysis."""

    return request_uri.startswith("/.well-known/")


def increment_not_found_count(
    summary: IpActivitySummary,
    request_uri: str,
) -> None:
    """Increment the appropriate 404 counter."""

    if FILE_SUFFIX_PATTERN.match(request_uri):
        summary.not_found_with_suffix += 1
    else:
        summary.not_found_without_suffix += 1


def is_successful_request(
    status: int,
    request_uri: str,
) -> bool:
    """Return True for non-root successful requests."""

    return status < 399 and request_uri != "/"


def is_authenticated_route(
    request_uri: str,
) -> bool:
    """Return True if the URI implies an authenticated user."""

    return any(
        fnmatch(request_uri, pattern) for pattern in PATH_PATTERNS_REQUIRING_LOGIN
    )


# ==================== GET IPS LOGIC ====================


def get_ips_to_block(
    summaries: dict[str, IpActivitySummary],
) -> list[str]:
    """Return suspicious IPs with no authenticated activity."""

    return [
        ip
        for ip, summary in summaries.items()
        if summary.is_suspicious and not summary.has_authenticated_activity
    ]


def get_ips_to_alert_on(
    summaries: dict[str, IpActivitySummary],
) -> list[str]:
    """Return suspicious IPs that also appear to be legitimate users."""

    return [
        ip
        for ip, summary in summaries.items()
        if summary.is_suspicious and summary.has_authenticated_activity
    ]


def create_metric_log_record(
    ips_to_alert_on: list[str],
) -> None:
    if len(ips_to_alert_on) > 0:
        print(
            f"authentication_breach_detected - success - Count of possible malicious IP addresses: {len(ips_to_alert_on)}"
        )


def update_dynamodb_table(ips: list[str]) -> None:
    dynamodb = boto3.client("dynamodb", region_name="eu-west-1")
    current_time = datetime.now(UTC)

    timeout_expiry_short = current_time + timedelta(minutes=30)
    timeout_expiry_medium = current_time + timedelta(hours=4)
    timeout_expiry_long = current_time + timedelta(hours=12)
    ttl = current_time + timedelta(hours=12)

    for ip in ips:
        response = dynamodb.get_item(TableName=table_name, Key={"IP": {"S": ip}})
        if "Item" in response:
            row_updated_at = datetime.fromtimestamp(
                int(response["Item"]["UpdatedAt"]["N"]), tz=UTC
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


def get_blocked_ips() -> list[str]:
    dynamodb = boto3.client("dynamodb", region_name="eu-west-1")
    response = dynamodb.scan(
        TableName=table_name, ProjectionExpression="IP, TimeoutExpiry"
    )
    current_time = datetime.now(UTC)
    ips = []
    for item in response["Items"]:
        if int(item["TimeoutExpiry"]["N"]) - int(current_time.timestamp()) >= 0:
            ips.append(item["IP"]["S"])

    return ips


def update_waf_ip_set(
    ip_set_name: str,
    ip_set_scope: str,
    ips: list[str],
) -> dict[str, Any]:
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
    log_stream_prefixes = [f"front.{environment}.web", f"admin.{environment}.web"]
    logs = query_cloudwatch_logs(log_group_name, log_stream_prefixes)
    summarised_logs = summarise_log_records(logs)
    print(summarised_logs)
    ips_to_block = get_ips_to_block(summarised_logs)
    print(f"New malicious IPs identified: {ips_to_block}")
    ips_to_alert_on = get_ips_to_alert_on(summarised_logs)
    print(f"New breach IPs identified: {ips_to_alert_on}")
    create_metric_log_record(ips_to_alert_on)
    update_dynamodb_table(ips_to_block)
    blocked_ips = get_blocked_ips()
    print(f"IPs to block according to dynamodb: {blocked_ips}")
    response = update_waf_ip_set(ip_set_name, ip_set_scope, blocked_ips)

    return response
