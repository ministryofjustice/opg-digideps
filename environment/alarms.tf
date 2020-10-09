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
  threshold           = 3
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.php_critical_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
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
  threshold           = 3
  period              = 3600
  evaluation_periods  = 1
  namespace           = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
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

//aws_cloudwatch_log_group.monitoring_lambda.name

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
  tags                = local.default_tags
}

data "aws_sns_topic" "availability-alert" {
  provider = aws.us-east-1
  name     = "availability-alert"
}

resource "aws_route53_health_check" "availability-front" {
  count             = local.account.always_on ? 1 : 0
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
  count               = local.account.always_on ? 1 : 0
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
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-front[0].id
  }
}

resource "aws_route53_health_check" "availability-admin" {
  count             = local.account.always_on ? 1 : 0
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
  count               = local.account.always_on ? 1 : 0
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
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-admin[0].id
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
  threshold           = 3
  period              = 3600
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.frontend_5xx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
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
  threshold           = 3
  period              = 3600
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.admin_5xx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
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
  threshold           = 3
  period              = 3600
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.api_5xx_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  tags                = local.default_tags
}


resource "aws_cloudwatch_metric_alarm" "frontend_alb_5xx_errors" {
  actions_enabled     = true
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  alarm_description   = "Number of 5XX Errors returned to Public Users from the ${local.environment} Frontend ALB."
  alarm_name          = "FrontendALB5XXErrors.${local.environment}"
  comparison_operator = "GreaterThanThreshold"
  dimensions = {
    "LoadBalancer" = trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/")
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

resource "aws_cloudwatch_metric_alarm" "admin_alb_5xx_errors" {
  actions_enabled     = true
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
  actions_enabled           = true
  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  alarm_description         = "Response Time for Frontend ALB in ${local.environment}"
  alarm_name                = "FrontendALBAverageResponseTime.${local.environment}"
  comparison_operator       = "GreaterThanUpperThreshold"
  datapoints_to_alarm       = 3
  evaluation_periods        = 60
  insufficient_data_actions = []
  treat_missing_data        = "notBreaching"
  threshold_metric_id       = "ad1"
  tags                      = local.default_tags

  metric_query {
    id          = "m1"
    return_data = true

    metric {
      dimensions = {
        "LoadBalancer" = trimprefix(split(":", aws_lb.front.arn)[5], "loadbalancer/")
      }
      metric_name = "TargetResponseTime"
      namespace   = "AWS/ApplicationELB"
      period      = 60
      stat        = "Average"
    }
  }

  metric_query {
    expression  = "ANOMALY_DETECTION_BAND(m1, 1)"
    id          = "ad1"
    label       = "TargetResponseTime (expected)"
    return_data = true
  }
}

resource "aws_cloudwatch_metric_alarm" "admin_alb_average_response_time" {
  actions_enabled           = true
  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  alarm_description         = "Response Time for Admin ALB in ${local.environment}"
  alarm_name                = "AdminALBAverageResponseTime.${local.environment}"
  comparison_operator       = "GreaterThanUpperThreshold"
  datapoints_to_alarm       = 3
  evaluation_periods        = 60
  insufficient_data_actions = []
  treat_missing_data        = "notBreaching"
  threshold_metric_id       = "ad1"
  tags                      = local.default_tags

  metric_query {
    id          = "m1"
    return_data = true

    metric {
      dimensions = {
        "LoadBalancer" = trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/")
      }
      metric_name = "TargetResponseTime"
      namespace   = "AWS/ApplicationELB"
      period      = 60
      stat        = "Average"
    }
  }

  metric_query {
    expression  = "ANOMALY_DETECTION_BAND(m1, 1)"
    id          = "ad1"
    label       = "TargetResponseTime (expected)"
    return_data = true
  }
}
