resource "aws_cloudwatch_log_metric_filter" "php_critical_errors" {
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

resource "aws_cloudwatch_metric_alarm" "php_critical_errors" {
  alarm_name          = "CriticalPHPErrors.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.php_critical_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.php_critical_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "php_errors" {
  name           = "PHPErrorFilter.${local.environment}"
  pattern        = "?\"[error]\" ?\"[crit]\" ?\"[alert]\" ?\"[emerg]\""
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "PHPErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "php_errors" {
  alarm_name          = "PHPErrors.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "queued_documents" {
  name           = "MonitorQueuedDocuments.${local.environment}"
  pattern        = "{ $.eventType = \"Queued_Documents\" }"
  log_group_name = aws_cloudwatch_log_group.monitoring_lambda.name

  metric_transformation {
    name      = "QueuedGreaterThanHour.${local.environment}"
    namespace = "DigiDeps/Error"
    value     = "$.count"
  }
}

resource "aws_cloudwatch_metric_alarm" "queued_documents" {
  alarm_name          = "QueuedDocsOver1Hr.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.queued_documents.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 1800
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.queued_documents.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags
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
  datapoints_to_alarm = 5
  threshold           = 1
  period              = 60
  evaluation_periods  = 5
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.availability-alert.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags

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
  datapoints_to_alarm = 5
  threshold           = 1
  period              = 60
  evaluation_periods  = 5
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.availability-alert.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-admin.id
  }
}

resource "aws_cloudwatch_log_metric_filter" "frontend_5xx_errors" {
  name           = "Frontend5XXErrors.${local.environment}"
  pattern        = "{($.service_name = \"frontend\") && ($.status = 5*)}"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "Frontend5XXErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "frontend_5xx_errors" {
  alarm_name          = "Frontend5XXErrors.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.frontend_5xx_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.frontend_5xx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "admin_5xx_errors" {
  name           = "Admin5XXErrors.${local.environment}"
  pattern        = "{($.service_name = \"admin\") && ($.status = 5*)}"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "Admin5XXErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "admin_5xx_errors" {
  alarm_name          = "Admin5XXErrors.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.admin_5xx_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.admin_5xx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "api_5xx_errors" {
  name           = "API5XXErrors.${local.environment}"
  pattern        = "{($.service_name = \"api\") && ($.status = 5*)}"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "API5XXErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "api_5xx_errors" {
  alarm_name          = "API5XXErrors.${local.environment}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.api_5xx_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.api_5xx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = local.account.alarms_active
  tags                = local.default_tags
}


resource "aws_cloudwatch_metric_alarm" "frontend_alb_5xx_errors" {
  actions_enabled     = local.account.alarms_active
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  alarm_description   = "Number of 5XX Errors returned to Public Users from the ${local.environment} Frontend ALB."
  alarm_name          = "FrontendALB5XXErrors.${local.environment}"
  comparison_operator = "GreaterThanThreshold"
  dimensions = {
    "LoadBalancer" = trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/")
  }
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  metric_name         = "HTTPCode_Target_5XX_Count"
  namespace           = "AWS/ApplicationELB"
  statistic           = "Sum"
  tags                = local.default_tags
  treat_missing_data  = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "admin_alb_5xx_errors" {
  actions_enabled     = local.account.alarms_active
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  alarm_description   = "Number of 5XX Errors returned to Internal Users from the ${local.environment} Admin ALB."
  alarm_name          = "AdminALB5XXErrors.${local.environment}"
  comparison_operator = "GreaterThanThreshold"
  dimensions = {
    "LoadBalancer" = trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/")
  }
  evaluation_periods = 1
  metric_name        = "HTTPCode_Target_5XX_Count"
  namespace          = "AWS/ApplicationELB"
  period             = 3600
  statistic          = "Sum"
  tags               = local.default_tags
  threshold          = 3
  treat_missing_data = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "frontend_alb_average_response_time" {
  actions_enabled     = local.account.alarms_active
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  alarm_description   = "Response Time for Frontend ALB in ${local.environment}"
  alarm_name          = "FrontendALBAverageResponseTime.${local.environment}"
  comparison_operator = "GreaterThanThreshold"
  dimensions = {
    "LoadBalancer" = trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/")
  }
  datapoints_to_alarm       = 7
  evaluation_periods        = 10
  threshold                 = 1
  period                    = 60
  namespace                 = "AWS/ApplicationELB"
  metric_name               = "TargetResponseTime"
  statistic                 = "Average"
  insufficient_data_actions = []
  treat_missing_data        = "notBreaching"
  tags                      = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "pre_registration_add_in_progress" {
  name           = "AdminCSVUploadInProgressFilter.${local.environment}"
  pattern        = "{ ($.service_name = \"admin\") && ($.request_uri = \"/admin/ajax/pre-registration-add*\") }"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "AdminCSVUploadInProgress.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "admin_alb_average_response_time" {
  alarm_name                = "AdminALBAverageResponseTime.${local.environment}"
  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  comparison_operator       = "GreaterThanOrEqualToThreshold"
  alarm_description         = "Response Time for Admin ALB in ${local.environment} (ignoring csv upload)"
  datapoints_to_alarm       = 7
  evaluation_periods        = 10
  threshold                 = 1
  insufficient_data_actions = []
  treat_missing_data        = "notBreaching"
  tags                      = local.default_tags

  metric_query {
    id          = "real_long_response"
    expression  = "IF((alb_response_times > 1 AND pre_registration_csv < 1), 1, 0)"
    label       = "LongResponseTime"
    return_data = "true"
  }

  metric_query {
    id = "alb_response_times"

    metric {
      metric_name = "TargetResponseTime"
      namespace   = "AWS/ApplicationELB"
      period      = "60"
      stat        = "Average"

      dimensions = {
        "LoadBalancer" = trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/")
      }
    }
  }

  metric_query {
    id = "pre_registration_csv"

    metric {
      metric_name = aws_cloudwatch_log_metric_filter.pre_registration_add_in_progress.metric_transformation[0].name
      namespace   = aws_cloudwatch_log_metric_filter.pre_registration_add_in_progress.metric_transformation[0].namespace
      period      = "60"
      stat        = "Maximum"
    }
  }
}

resource "aws_cloudwatch_metric_alarm" "admin_ddos_attack_external" {
  alarm_name          = "AdminDDoSDetected"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "3"
  metric_name         = "DDoSDetected"
  namespace           = "AWS/DDoSProtection"
  period              = "60"
  statistic           = "Average"
  threshold           = "0"
  alarm_description   = "Triggers when AWS Shield Advanced detects a DDoS attack"
  treat_missing_data  = "notBreaching"
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  dimensions = {
    ResourceArn = aws_lb.admin.arn
  }
}

resource "aws_cloudwatch_metric_alarm" "front_ddos_attack_external" {
  alarm_name          = "FrontDDoSDetected"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "3"
  metric_name         = "DDoSDetected"
  namespace           = "AWS/DDoSProtection"
  period              = "60"
  statistic           = "Average"
  threshold           = "0"
  alarm_description   = "Triggers when AWS Shield Advanced detects a DDoS attack"
  treat_missing_data  = "notBreaching"
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  dimensions = {
    ResourceArn = aws_lb.front.arn
  }
}
