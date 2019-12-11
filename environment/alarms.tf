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

resource "aws_sns_topic" "availability-alert" {
  provider     = aws.us-east-1
  name         = "${local.environment}-${terraform.workspace}-alert"
  display_name = "${local.default_tags["application"]} ${local.environment} Alert"
}

resource "aws_route53_health_check" "availability-front" {
  fqdn                  = aws_route53_record.front.fqdn
  resource_path         = "/manage/availability"
  port                  = 443
  type                  = "HTTPS"
  failure_threshold     = 1
  request_interval      = 30
  measure_latency       = true
  cloudwatch_alarm_name = "availability-front-healthcheck"
  tags                  = merge(local.default_tags, { Name = "availability-front" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-front" {
  provider            = aws.us-east-1
  alarm_name          = "${local.default_tags["application"]}-availability-front"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  threshold           = 1
  period              = 300
  datapoints_to_alarm = 1
  evaluation_periods  = 288
  namespace           = "AWS/Route53"
  alarm_actions       = [aws_sns_topic.availability-alert.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-front.id
  }
}

resource "aws_route53_health_check" "availability-admin" {
  fqdn                  = aws_route53_record.admin.fqdn
  resource_path         = "/manage/availability"
  port                  = 443
  type                  = "HTTPS"
  failure_threshold     = 1
  request_interval      = 30
  measure_latency       = true
  cloudwatch_alarm_name = "availability-admin-healthcheck"
  tags                  = merge(local.default_tags, { Name = "availability-admin" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-admin" {
  provider            = aws.us-east-1
  alarm_name          = "${local.default_tags["application"]}-availability-admin"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  threshold           = 1
  period              = 300
  datapoints_to_alarm = 1
  evaluation_periods  = 288
  namespace           = "AWS/Route53"
  alarm_actions       = [aws_sns_topic.availability-alert.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-admin.id
  }
}
