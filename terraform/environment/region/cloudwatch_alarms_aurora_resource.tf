locals {
  aurora_unusual_read_throughput_minimum  = 1000000 # Minimum read throughput considered unusual
  aurora_unusual_network_transmit_minimum = 1000000 # Minimum network transmit considered unusual
  aurora_unusual_read_iops_minimum        = 300     # Minimum read IOPS considered unusual
  aurora_anomaly_detection_band_width     = 12      # The higher this value, the less sensitive the anomaly detection will be, while lower values make it more sensitive (and prone to more false alarms)
}

resource "aws_cloudwatch_metric_alarm" "aurora_high_cpu" {
  for_each = module.database.instance_identifiers

  alarm_name          = "${local.environment}-aurora-${each.key}-high-cpu"
  alarm_description   = "Aurora instance ${each.value} has had average CPU utilisation at or above 85% for 45 minutes."
  namespace           = "AWS/RDS"
  metric_name         = "CPUUtilization"
  statistic           = "Average"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"

  dimensions = {
    DBInstanceIdentifier = each.value
  }

  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  ok_actions                = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.environment.resource_alarms_active
  tags                      = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "aurora_high_acu" {
  for_each = module.database.instance_identifiers

  alarm_name          = "${local.environment}-aurora-${each.key}-high-acu"
  alarm_description   = "Aurora instance ${each.value} has used at least 85% of its configured maximum ACU capacity for 45 minutes."
  namespace           = "AWS/RDS"
  metric_name         = "ACUUtilization"
  statistic           = "Average"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"

  dimensions = {
    DBInstanceIdentifier = each.value
  }

  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  ok_actions                = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.environment.resource_alarms_active
  tags                      = var.default_tags
}

# AuroraDB Exfiltration Alarms

resource "aws_cloudwatch_log_metric_filter" "database_activity_in_progress" {
  name           = "DatabaseActivityInProgress.${local.environment}"
  pattern        = "database_activity_in_progress"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "DatabaseActivityInProgress.${local.environment}"
    namespace     = "DigiDeps/Database"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "aurora_unusual_throughput" {
  for_each = var.account.environment.is_production == 1 ? module.database.instance_identifiers : tomap({})

  alarm_name          = "${local.environment}-aurora-${each.key}-unusual-throughput"
  alarm_description   = "Aurora instance ${each.value} has experienced unusually high read or network transmit throughput for at least 10 minutes."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 2
  datapoints_to_alarm = 2
  threshold           = 1
  treat_missing_data  = "notBreaching"

  metric_query {
    id          = "unusual_throughput"
    expression  = "IF(FILL(database_activity_running, 0) > 0, 0, IF((read_throughput > normal_read_throughput_limit AND read_throughput > ${local.aurora_unusual_read_throughput_minimum}) OR (network_transmit > normal_network_transmit_limit AND network_transmit > ${local.aurora_unusual_network_transmit_minimum}), 1, 0))"
    label       = "Unusual database throughput"
    return_data = true
  }

  metric_query {
    id          = "normal_read_throughput_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(read_throughput, ${local.aurora_anomaly_detection_band_width}))"
    label       = "Normal read throughput limit"
    return_data = false
  }

  metric_query {
    id          = "normal_network_transmit_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(network_transmit, ${local.aurora_anomaly_detection_band_width}))"
    label       = "Normal network transmit limit"
    return_data = false
  }

  metric_query {
    id          = "read_throughput"
    return_data = false

    metric {
      namespace   = "AWS/RDS"
      metric_name = "ReadThroughput"
      period      = 300
      stat        = "Average"

      dimensions = {
        DBInstanceIdentifier = each.value
      }
    }
  }

  metric_query {
    id          = "network_transmit"
    return_data = false

    metric {
      namespace   = "AWS/RDS"
      metric_name = "NetworkTransmitThroughput"
      period      = 300
      stat        = "Average"

      dimensions = {
        DBInstanceIdentifier = each.value
      }
    }
  }

  metric_query {
    id          = "database_activity_running"
    return_data = false

    metric {
      namespace   = aws_cloudwatch_log_metric_filter.database_activity_in_progress.metric_transformation[0].namespace
      metric_name = aws_cloudwatch_log_metric_filter.database_activity_in_progress.metric_transformation[0].name
      period      = 300
      stat        = "Maximum"
    }
  }

  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  ok_actions                = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.environment.alarms_active
  tags                      = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "aurora_unusual_read_iops" {
  for_each = var.account.environment.is_production == 1 ? module.database.instance_identifiers : tomap({})

  alarm_name          = "${local.environment}-aurora-${each.key}-unusual-read-iops"
  alarm_description   = "Aurora instance ${each.value} has experienced unusually high read IOPS for at least 10 minutes."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 2
  datapoints_to_alarm = 2
  threshold           = 1
  treat_missing_data  = "notBreaching"

  metric_query {
    id          = "unusual_read_iops"
    expression  = "IF(FILL(database_activity_running, 0) > 0, 0, IF(read_iops > normal_read_iops_limit AND read_iops > ${local.aurora_unusual_read_iops_minimum}, 1, 0))"
    label       = "Unusual read IOPS"
    return_data = true
  }

  metric_query {
    id          = "normal_read_iops_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(read_iops, ${local.aurora_anomaly_detection_band_width}))"
    label       = "Normal read IOPS limit"
    return_data = false
  }

  metric_query {
    id          = "read_iops"
    return_data = false

    metric {
      namespace   = "AWS/RDS"
      metric_name = "ReadIOPS"
      period      = 300
      stat        = "Average"

      dimensions = {
        DBInstanceIdentifier = each.value
      }
    }
  }

  metric_query {
    id          = "database_activity_running"
    return_data = false

    metric {
      namespace   = aws_cloudwatch_log_metric_filter.database_activity_in_progress.metric_transformation[0].namespace
      metric_name = aws_cloudwatch_log_metric_filter.database_activity_in_progress.metric_transformation[0].name
      period      = 300
      stat        = "Maximum"
    }
  }

  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  ok_actions                = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.environment.alarms_active
  tags                      = var.default_tags
}
