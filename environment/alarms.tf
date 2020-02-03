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

data "aws_sns_topic" "availability-alert" {
  provider = aws.us-east-1
  name     = "availability-alert"
}

resource "aws_route53_health_check" "availability-front" {
  fqdn              = aws_route53_record.front.fqdn
  resource_path     = "/manage/availability"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-front" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-front" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-front"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 1
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.availability-alert.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-front.id
  }
}

resource "aws_route53_health_check" "availability-admin" {
  fqdn              = aws_route53_record.admin.fqdn
  resource_path     = "/manage/availability"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-admin" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-admin" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-admin"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 1
  threshold           = 1
  period              = 3600
  evaluation_periods  = 1
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.availability-alert.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-admin.id
  }
}

module "notify_slack_availability" {
  source  = "terraform-aws-modules/notify-slack/aws"
  version = "~> 2.0"

  providers = {
    aws = aws.us-east-1
  }

  sns_topic_name   = data.aws_sns_topic.availability-alert.name
  create_sns_topic = false
  create           = ! (local.account.dynamic)

  lambda_function_name = "notify-slack-${local.environment}"

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_webhook_url.secret_string
  slack_channel     = local.account.is_production == 1 ? "#opg-digideps-team" : "#opg-digideps-devs"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}
