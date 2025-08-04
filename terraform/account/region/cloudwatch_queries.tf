locals {
  default_insights_query_log_identifier = {
    production    = "production02",
    preproduction = "integration",
    development   = "development"
  }
}

resource "aws_cloudwatch_query_definition" "non_healthcheck_requests" {
  name            = "Analysis/App-Services-No-Healthchecks"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: General view of logs without the healthchecks
# Usage: Useful for general view of what's happening with less noise
fields @timestamp, service_name, request_uri, status, @message
| sort @timestamp desc
| filter ispresent(service_name)
| filter request_uri NOT LIKE /health-check/
QUERY
}

resource "aws_cloudwatch_query_definition" "exceptions_and_errors" {
  name            = "Analysis/Error-Exceptions"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: Display application errors from logs
# Usage: Useful for diagnosing application problems
fields @timestamp, level, message, @message
| sort @timestamp desc
| filter tolower(@message) like /exception|error|critical/
| filter @message not like 'verbose.NOTICE'
QUERY
}

resource "aws_cloudwatch_query_definition" "slow_response_times" {
  name            = "Analysis/Requests-With-Slow-Response-Times"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: Shows requests with response time of more than 2 seconds
# Usage: Identify which areas of the app are performing slowly
fields @timestamp, service_name, upstream_response_time, request_uri, status, real_forwarded_for
| filter upstream_response_time > 2.0
| sort upstream_response_time desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "status_5xx" {
  name            = "Analysis/Requests-With-5xx-Status"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: 5xx webserver responses and messages that contain error strings
# Usage: Look for 5xx errors in status column and find likely related errors with similar timestamp
fields @timestamp, @logStream, status, service_name, request_uri, message, @message
| filter ((!ispresent(status) and tolower(@message) like /exception|error|critical/ and @message not like /NOTICE|open()/) or status > 499)
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "status_4xx" {
  name            = "Analysis/Requests-With-4xx-Status"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: 4xx webserver responses
# Usage: Look for unusual request_uris or increases of particular status over time
fields @timestamp, status, service_name, request_uri, upstream_response_time
| filter (status > 399 and status < 500)
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "status_404" {
  name            = "Analysis/Requests-With-404-Status"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: 404 webserver responses
# Usage: Look for unusual request_uris that get 404 along with IP address
fields @timestamp, request_uri, status, real_forwarded_for
| filter @logStream like 'front_web'
| filter status = 404
| sort @timestamp desc
| limit 10000
QUERY
}

resource "aws_cloudwatch_query_definition" "status_404_counts_per_10_mins" {
  name            = "Analysis/Requests-With-404-Status"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: 404 webserver responses counts
# Usage: Look for unusually high counts of 404s in a 10 minute period
fields @timestamp, request_uri, status, real_forwarded_for
| filter @logStream like 'front_web'
| filter status = 404
| stats count() as count_404s by bin(10m)
| sort count_404s desc
QUERY
}

resource "aws_cloudwatch_query_definition" "critical" {
  name            = "Analysis/Critical-Level-Application-Errors"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: Search for critical level error messages
# Usage: Find the error and look at the request.path and msg for details
fields @timestamp, request.path, msg, @message
| filter level = 'CRITICAL'
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "response_distribution" {
  name            = "Analysis/Response-Distribution-By-Status"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: Get an idea of response distribution compared to baseline
# Usage: Run against set timeframe now and similar timeframe from the day before and compare
fields service_name, status
| stats count() as count by service_name, status
| sort by service_name, status
QUERY
}

resource "aws_cloudwatch_query_definition" "sync_logs" {
  name            = "Document-Sync/All-Document-And-Checklist-Sync-Logs"
  log_group_names = [local.default_insights_query_log_identifier[var.account.name]]

  query_string = <<QUERY
# Purpose: Show all logs for document and checklist sync
# Usage: Check if no records or errors are appearing in logs
fields @timestamp, @message, @logStream
| sort @timestamp desc
| filter @logStream like /checklist-sync|document-sync/
QUERY
}

resource "aws_cloudwatch_query_definition" "container_cpu_memory" {
  name            = "ECS-Statistics/Container-Cpu-Memory"
  log_group_names = ["/aws/ecs/containerinsights/${local.default_insights_query_log_identifier[var.account.name]}/performance"]

  query_string = <<QUERY
# Purpose: Container CPU and Memory Stats
# Usage: Initial insights on performance. For better visualization, see the metric graphs in ECS
fields @timestamp, ContainerName, TaskId, CpuUtilized, MemoryUtilized, Image
| filter ServiceName like /front-production02|admin-production02|api-production02/
| filter Type like /Container/
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "container_high_cpu_memory_only" {
  name            = "ECS-Statistics/Container-High-CPU-Memory-Only"
  log_group_names = ["/aws/ecs/containerinsights/${local.default_insights_query_log_identifier[var.account.name]}/performance"]

  query_string = <<QUERY
# Purpose: High Container CPU and Memory Stats
# Usage: See which containers are under load. For better visualization, see the metric graphs in ECS
fields @timestamp, ContainerName, TaskId, CpuUtilized, MemoryUtilized, Image, @message
| filter Type like 'Container'
| filter (CpuUtilized > 80 or MemoryUtilized > 80)
| filter ContainerName not like /rest/
| sort @timestamp desc
| limit 1000
QUERY
}

resource "aws_cloudwatch_query_definition" "container_turnover" {
  name            = "ECS-Statistics/Container-Turnover"
  log_group_names = ["/aws/ecs/containerinsights/${local.default_insights_query_log_identifier[var.account.name]}/performance"]

  query_string = <<QUERY
# Purpose: Shows when main containers were last provisioned
# Usage: Find out if errors (found using different queries) coincide with container restarts
# Notes: More detailed information available in ECS
fields @timestamp, ServiceName, DesiredTaskCount, RunningTaskCount, PendingTaskCount
| filter ServiceName like /front-production02|admin-production02|api-production02/
| filter Type like /Service/
| filter PendingTaskCount > 0
| sort @timestamp desc
| limit 1000
QUERY
}

# WAF Logs
resource "aws_cloudwatch_query_definition" "ac1_blocked_by_rule" {
  name            = "WAF/Blocked Requests by Rule"
  log_group_names = [aws_cloudwatch_log_group.waf_web_acl.name]

  query_string = <<QUERY
fields @message
| filter @message like /"action":"BLOCK"/
| parse @message /"labels":\[\{"name":"[^"]+:(?<category>[^"]+)"/
| parse @message /"terminatingRuleId":"(?<rule_id>[^"]+)"/
| stats count() as blocked_requests by coalesce(category, rule_id, "Unknown")
| sort blocked_requests desc
QUERY
}

resource "aws_cloudwatch_query_definition" "ac2_blocked_ips_with_reason" {
  name            = "WAF/Blocked IPs with Reason"
  log_group_names = [aws_cloudwatch_log_group.waf_web_acl.name]

  query_string = <<QUERY
fields @message
| filter @message like /"action":"BLOCK"/
| parse @message /"clientIp":"(?<client_ip>[^"]+)"/
| parse @message /"labels":\[\{"name":"[^"]+:(?<category>[^"]+)"/
| parse @message /"terminatingRuleId":"(?<rule_id>[^"]+)"/
| stats count() as blocked_requests by client_ip, coalesce(category, rule_id, "Unknown") as reason
| sort blocked_requests desc
QUERY
}

resource "aws_cloudwatch_query_definition" "ac3_detailed_blocked_requests" {
  name            = "WAF/Detailed Blocked Requests"
  log_group_names = [aws_cloudwatch_log_group.waf_web_acl.name]

  query_string = <<QUERY
fields @message
| filter @message like /"action":"BLOCK"/
| parse @message /"clientIp":"(?<client_ip>[^"]+)"/
| parse @message /"country":"(?<country>[^"]+)"/
| parse @message /"uri":"(?<uri>[^"]+)"/
| parse @message /"httpMethod":"(?<method>[^"]+)"/
| parse @message /"args":"(?<args>[^"]+)"/
| parse @message /"terminatingRuleId":"(?<rule_id>[^"]+)"/
| parse @message /"host":"(?<host>[^"]+)"/
| parse @message /"User-Agent","value":"(?<user_agent>[^"]+)"/
| stats count() as blocked_requests by client_ip, country, method, uri, args, host, user_agent, rule_id
| sort blocked_requests desc
| limit 100
QUERY
}
