resource "aws_sns_topic" "alert" {
  name         = "${local.environment}-${terraform.workspace}-alert"
  display_name = "${local.default_tags["application"]} ${local.environment} Alert"
}

resource "aws_cloudwatch_log_metric_filter" "php_errors" {
  name           = "CriticalPHPErrorFilter.${local.environment}"
  pattern        = "CRITICAL"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "CriticalPHPErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "php_errors" {
  alarm_name          = "CriticalPHPErrors.${local.environment}"
  statistic           = "Sum"
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
  pattern        = "?\"[error]\" ?\"[crit]\" ?\"[alert]\" ?\"[emerg]\""
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "CriticalNginxErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "nginx_errors" {
  alarm_name          = "CriticalNginxErrors.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.nginx_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.nginx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
}

resource "aws_sns_topic" "acs-test" {
  provider     = aws.us-east-1
  name         = "${local.environment}-${terraform.workspace}-alert"
  display_name = "${local.default_tags["application"]} ${local.environment} Alert"
}

resource "aws_route53_health_check" "availability" {
  fqdn                  = aws_route53_record.front.fqdn
  resource_path         = "/manage/availability"
  port                  = 443
  type                  = "HTTPS"
  failure_threshold     = 1
  request_interval      = 30
  measure_latency       = true
  cloudwatch_alarm_name = "availability-healthcheck"
  tags = merge(
    local.default_tags,
    {
      Name = "availability"
    },
  )

}

resource "aws_cloudwatch_metric_alarm" "availability" {
  provider            = aws.us-east-1
  alarm_name          = "availability"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  threshold           = 1
  period              = 300
  datapoints_to_alarm = 1
  evaluation_periods  = 288
  namespace           = "AWS/Route53"
  alarm_actions       = [aws_sns_topic.acs-test.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability.id
  }
}
