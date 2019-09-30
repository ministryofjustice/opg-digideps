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
  name           = HTTP5xxError
  pattern        = "[..., status_code=5*]"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name      = "FatalPHPErrors"
    namespace = "DigiDeps/Error"
    value     = "1"
  }
}
