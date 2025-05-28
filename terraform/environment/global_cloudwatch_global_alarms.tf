# ========== Healthcheck related Alarms ==========

data "aws_sns_topic" "availability_alert" {
  provider = aws.us-east-1
  name     = "availability-alert-${local.primary_region_name}"
}

resource "aws_route53_health_check" "availability_front" {
  fqdn              = local.front_fqdn
  resource_path     = "/health-check"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-front" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability_front" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-front"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 3
  threshold           = 1
  period              = 60
  evaluation_periods  = 3
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.availability_alert.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability_front.id
  }
}

resource "aws_route53_health_check" "availability_admin" {
  fqdn              = local.admin_fqdn
  resource_path     = "/health-check"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-admin" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability_admin" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-admin"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 3
  threshold           = 1
  period              = 60
  evaluation_periods  = 3
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.availability_alert.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability_admin.id
  }
}
