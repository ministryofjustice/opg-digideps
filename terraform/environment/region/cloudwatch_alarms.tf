# Prerequisite SNS topic
data "aws_sns_topic" "alerts" {
  name = "alerts"
}

# ========== Log Error Alarms ==========

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
  alarm_name          = "${local.environment}-critical-php-errors"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.php_critical_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.php_critical_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
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
  alarm_name          = "${local.environment}-php-errors"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 5
  evaluation_periods  = 5
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.php_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}

# ========== Log response status alarms ==========

resource "aws_cloudwatch_log_metric_filter" "frontend_5xx_errors" {
  name           = "Frontend5XXErrors.${local.environment}"
  pattern        = "{($.service_name = \"frontend\") && ($.status = 5*) && ($.request_uri != \"/health-check/dependencies\")}"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "Frontend5XXErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "frontend_5xx_errors" {
  alarm_name          = "${local.environment}-frontend-5xx-errors"
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
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "admin_5xx_errors" {
  name           = "Admin5XXErrors.${local.environment}"
  pattern        = "{($.service_name = \"admin\") && ($.status = 5*) && ($.request_uri != \"/health-check/dependencies\")}"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "Admin5XXErrors.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "admin_5xx_errors" {
  alarm_name          = "${local.environment}-admin-5xx-errors"
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
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
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
  alarm_name          = "${local.environment}-api-5xx-errors"
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
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}

# ========== Load balancer status response alarms ==========

resource "aws_cloudwatch_metric_alarm" "frontend_alb_5xx_errors" {
  alarm_name          = "${local.environment}-frontend-alb-5xx-errors"
  alarm_description   = "Number of 5XX Errors returned to Public Users from the ${local.environment} Frontend ALB."
  actions_enabled     = var.account.alarms_active
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
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
  tags                = var.default_tags
  treat_missing_data  = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "admin_alb_5xx_errors" {
  alarm_name          = "${local.environment}-admin-alb-5xx-errors"
  alarm_description   = "Number of 5XX Errors returned to Internal Users from the ${local.environment} Admin ALB."
  actions_enabled     = var.account.alarms_active
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  comparison_operator = "GreaterThanThreshold"
  dimensions = {
    "LoadBalancer" = trimprefix(split(":", aws_lb.admin.arn)[5], "loadbalancer/")
  }
  evaluation_periods = 1
  metric_name        = "HTTPCode_Target_5XX_Count"
  namespace          = "AWS/ApplicationELB"
  period             = 3600
  statistic          = "Sum"
  tags               = var.default_tags
  threshold          = 3
  treat_missing_data = "notBreaching"
}

# ========== Response time alarms ==========

resource "aws_cloudwatch_metric_alarm" "frontend_alb_average_response_time" {
  alarm_name          = "${local.environment}-frontend-alb-response-time"
  alarm_description   = "Response Time for Frontend ALB in ${local.environment}"
  actions_enabled     = var.account.alarms_active
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
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
  tags                      = var.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "pre_registration_add_in_progress" {
  name           = "AdminCSVUploadInProgressFilter.${local.environment}"
  pattern        = "{ ($.service_name = \"api\") && ($.request_uri = \"/v2/org-deputyships\") }"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "AdminCSVUploadInProgress.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "admin_alb_average_response_time" {
  alarm_name                = "${local.environment}-admin-alb-response-time"
  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  comparison_operator       = "GreaterThanOrEqualToThreshold"
  alarm_description         = "Response Time for Admin ALB in ${local.environment} (ignoring csv upload)"
  datapoints_to_alarm       = 7
  evaluation_periods        = 10
  threshold                 = 1
  insufficient_data_actions = []
  treat_missing_data        = "notBreaching"
  tags                      = var.default_tags

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

# ========== DDOS Alarms ==========

resource "aws_cloudwatch_metric_alarm" "admin_ddos_attack_external" {
  alarm_name          = "${local.environment}-admin-ddos-detected"
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
  alarm_name          = "${local.environment}-front-ddos-detected"
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

# Document Sync Alerts

resource "aws_cloudwatch_log_metric_filter" "document_queued_more_than_hour" {
  name           = "DocumentQueuedError.${local.environment}"
  pattern        = "[ts, ll = \"*NOTICE*\", q = \"queued_over_1_hour\", qv, p, pv, t, tv, e, ev, x1, x2]"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "DocumentQueuedError.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "$qv"
    default_value = "0"
  }
}

resource "aws_cloudwatch_log_metric_filter" "document_in_progress_more_than_hour" {
  name           = "DocumentProgressError.${local.environment}"
  pattern        = "[ts, ll = \"*NOTICE*\", q = \"queued_over_1_hour\", qv, p, pv, t, tv, e, ev, x1, x2]"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "DocumentProgressError.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "$pv"
    default_value = "0"
  }
}

resource "aws_cloudwatch_log_metric_filter" "document_temporary_error" {
  name           = "DocumentTemporaryError.${local.environment}"
  pattern        = "[ts, ll = \"*NOTICE*\", q = \"queued_over_1_hour\", qv, p, pv, t, tv, e, ev, x1, x2]"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "DocumentTemporaryError.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "$tv"
    default_value = "0"
  }
}

resource "aws_cloudwatch_log_metric_filter" "document_permanent_error" {
  name           = "DocumentPermanentError.${local.environment}"
  pattern        = "[ts, ll = \"*NOTICE*\", q = \"queued_over_1_hour\", qv, p, pv, t, tv, e, ev, x1, x2]"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "DocumentPermanentError.${local.environment}"
    namespace     = "DigiDeps/Error"
    value         = "$ev"
    default_value = "0"
  }
}

# Adding unrealistically high thresholds at the moment as we have to clear up some old document data
resource "aws_cloudwatch_metric_alarm" "document_queued_more_than_hour" {
  alarm_name          = "${local.environment}-document-queued-over-1hr"
  statistic           = "Maximum"
  metric_name         = aws_cloudwatch_log_metric_filter.document_queued_more_than_hour.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 300
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.document_queued_more_than_hour.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "document_progress_more_than_hour" {
  alarm_name          = "${local.environment}-document-progress-over-1hr"
  statistic           = "Maximum"
  metric_name         = aws_cloudwatch_log_metric_filter.document_in_progress_more_than_hour.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 300
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.document_in_progress_more_than_hour.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "document_temporary_error" {
  alarm_name          = "${local.environment}-document-temporary-error"
  statistic           = "Maximum"
  metric_name         = aws_cloudwatch_log_metric_filter.document_temporary_error.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 300
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.document_temporary_error.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "document_permanent_error" {
  alarm_name          = "${local.environment}-document-permanent-error"
  statistic           = "Maximum"
  metric_name         = aws_cloudwatch_log_metric_filter.document_permanent_error.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 1
  period              = 300
  evaluation_periods  = 1
  treat_missing_data  = "notBreaching"
  namespace           = aws_cloudwatch_log_metric_filter.document_permanent_error.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alerts.arn]
  actions_enabled     = var.account.alarms_active
  tags                = var.default_tags
}
