resource "aws_cloudwatch_log_metric_filter" "waf_blocked_requests" {
  name           = "WAFBlockedRequests.${var.account.name}"
  log_group_name = "aws-waf-logs-${var.account.name}"

  pattern = "{ $.action = \"BLOCK\" }"

  metric_transformation {
    name          = "WAFBlockedRequests.${var.account.name}"
    namespace     = "DigiDeps/WAF"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "waf_blocked_requests_high_volume" {
  alarm_name        = "${var.account.name}-waf-high-block-volume"
  alarm_description = "Triggers when AWS WAF blocks more than 25,000 requests within 5 minutes, indicating a potential attack, bot activity, or misconfiguration."

  namespace   = aws_cloudwatch_log_metric_filter.waf_blocked_requests.metric_transformation[0].namespace
  metric_name = aws_cloudwatch_log_metric_filter.waf_blocked_requests.metric_transformation[0].name

  statistic           = "Sum"
  period              = 300
  evaluation_periods  = 1
  datapoints_to_alarm = 1

  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 25000

  alarm_actions      = [aws_sns_topic.alerts.arn]
  ok_actions         = var.account.pagerduty_enabled ? [aws_sns_topic.alerts.arn] : []
  treat_missing_data = "notBreaching"
  actions_enabled    = true

  tags = var.default_tags
}
