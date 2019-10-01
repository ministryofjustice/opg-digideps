resource "aws_cloudwatch_metric_alarm" "ses_bounce_1h" {
  alarm_name          = "SESBounceRate"
  statistic           = "Average"
  metric_name         = "Reputation.BounceRate"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 0.05
  period              = 3600
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/SES"
  alarm_actions       = [aws_sns_topic.alerts.arn]
}

resource "aws_cloudwatch_metric_alarm" "ses_complaint_1h" {
  alarm_name          = "SESComplaintRate"
  statistic           = "Average"
  metric_name         = "Reputation.ComplaintRate"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 0.001
  period              = 3600
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/SES"
  alarm_actions       = [aws_sns_topic.alerts.arn]
}

resource "aws_cloudwatch_log_metric_filter" "php_errors" {
  name           = "CriticalPHPErrorFilter"
  pattern        = "CRITICAL"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name      = "CriticalPHPErrors"
    namespace = "DigiDeps/Error"
    value     = "1"
  }
}

resource "aws_cloudwatch_metric_alarm" "php_errors" {
  alarm_name          = "CriticalPHPErrors"
  statistic           = "SampleCount"
  metric_name         = aws_cloudwatch_log_metric_filter.php_errors.name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].namespace
  alarm_actions       = [aws_sns_topic.alerts.arn]
}

resource "aws_cloudwatch_log_metric_filter" "nginx_errors" {
  name           = "CriticalNginxErrorFilter"
  pattern        = "?error ?crit ?alert ?emerg"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name      = "CriticalNginxErrors"
    namespace = "DigiDeps/Error"
    value     = "1"
  }
}

resource "aws_cloudwatch_metric_alarm" "nginx_errors" {
  alarm_name          = "CriticalNginxErrors"
  statistic           = "SampleCount"
  metric_name         = aws_cloudwatch_log_metric_filter.nginx_errors.name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.nginx_errors.metric_transformation[0].namespace
  alarm_actions       = [aws_sns_topic.alerts.arn]
}
