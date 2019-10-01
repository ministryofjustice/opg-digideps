resource "aws_cloudwatch_log_metric_filter" "php_errors" {
  name           = "CriticalPHPErrorFilter.${local.environment}"
  pattern        = "CRITICAL"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name      = "CriticalPHPErrors.${local.environment}"
    namespace = "DigiDeps/Error"
    value     = "1"
  }
}

resource "aws_cloudwatch_metric_alarm" "php_errors" {
  alarm_name          = "CriticalPHPErrors.${local.environment}"
  statistic           = "SampleCount"
  metric_name         = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
}

resource "aws_cloudwatch_log_metric_filter" "nginx_errors" {
  name           = "CriticalNginxErrorFilter.${local.environment}"
  pattern        = "?error ?crit ?alert ?emerg"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name      = "CriticalNginxErrors.${local.environment}"
    namespace = "DigiDeps/Error"
    value     = "1"
  }
}

resource "aws_cloudwatch_metric_alarm" "nginx_errors" {
  alarm_name          = "CriticalNginxErrors.${local.environment}"
  statistic           = "SampleCount"
  metric_name         = aws_cloudwatch_log_metric_filter.nginx_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.nginx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
}
