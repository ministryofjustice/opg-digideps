resource "aws_cloudwatch_event_rule" "monitoring_db_queries" {
  name                = "monitor-rds-${local.environment}"
  description         = "Runs bespoke monitoring SQL statements against RDS DB"
  schedule_expression = "rate(5 minutes)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "monitoring_db_queries" {
  rule  = aws_cloudwatch_event_rule.monitoring_db_queries.name
  arn   = aws_lambda_function.monitoring.arn
  input = <<EOF
    {
      "check_name": "queued_documents"
    }
  EOF
}

resource "aws_lambda_permission" "allow_cloudwatch_call_monitor_lambda" {
  statement_id  = "AllowExecutionFrom-${aws_cloudwatch_event_rule.monitoring_db_queries.name}"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.monitoring.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.monitoring_db_queries.arn
}

resource "aws_cloudwatch_log_metric_filter" "queued_documents" {
  name           = "MonitorQueuedDocuments.${local.environment}"
  pattern        = "{ $.eventType = \"Queued_Documents\" }"
  log_group_name = aws_cloudwatch_log_group.monitoring_lambda.name

  metric_transformation {
    name          = "QueuedGreaterThanHour.${local.environment}"
    namespace     = "Monitoring"
    value         = "$.count"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "queued_documents" {
  alarm_name          = "QueuedDocsOver1Hr.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 1800
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
}
