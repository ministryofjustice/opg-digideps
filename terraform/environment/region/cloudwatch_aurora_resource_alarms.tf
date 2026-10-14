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

resource "aws_cloudwatch_log_metric_filter" "database_analyse_in_progress" {
  name           = "DatabaseAnalyseInProgress.${local.environment}"
  pattern        = "database_analyse_in_progress"
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name

  metric_transformation {
    name          = "DatabaseAnalyseInProgress.${local.environment}"
    namespace     = "DigiDeps/Database"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "aurora_unusual_throughput" {
  for_each = module.database.instance_identifiers

  alarm_name          = "${local.environment}-aurora-${each.key}-unusual-throughput"
  alarm_description   = "Aurora instance ${each.value} has experienced unusually high read or network transmit throughput for at least 10 minutes."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 2
  datapoints_to_alarm = 2
  threshold           = 1
  treat_missing_data  = "notBreaching"

  metric_query {
    id          = "unusual_throughput"
    expression  = "IF(read_throughput > read_throughput_limit OR network_transmit > network_transmit_limit, 1, 0)"
    label       = "Unusual database throughput"
    return_data = true
  }

  metric_query {
    id          = "read_throughput_limit"
    expression  = "IF(FILL(database_analyse_running, 0) > 0, normal_read_throughput_limit * 2, normal_read_throughput_limit)"
    label       = "Read throughput limit"
    return_data = false
  }

  metric_query {
    id          = "normal_read_throughput_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(read_throughput, 6))"
    label       = "Normal read throughput limit"
    return_data = false
  }

  metric_query {
    id          = "network_transmit_limit"
    expression  = "IF(FILL(database_analyse_running, 0) > 0, normal_network_transmit_limit * 2, normal_network_transmit_limit)"
    label       = "Network transmit limit"
    return_data = false
  }

  metric_query {
    id          = "normal_network_transmit_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(network_transmit, 6))"
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
    id          = "database_analyse_running"
    return_data = false

    metric {
      namespace   = aws_cloudwatch_log_metric_filter.database_analyse_in_progress.metric_transformation[0].namespace
      metric_name = aws_cloudwatch_log_metric_filter.database_analyse_in_progress.metric_transformation[0].name
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
  for_each = module.database.instance_identifiers

  alarm_name          = "${local.environment}-aurora-${each.key}-unusual-read-iops"
  alarm_description   = "Aurora instance ${each.value} has experienced unusually high read IOPS for at least 10 minutes."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 2
  datapoints_to_alarm = 2
  threshold           = 1
  treat_missing_data  = "notBreaching"

  metric_query {
    id          = "unusual_read_iops"
    expression  = "IF(read_iops > read_iops_limit, 1, 0)"
    label       = "Unusual read IOPS"
    return_data = true
  }

  metric_query {
    id          = "read_iops_limit"
    expression  = "IF(FILL(database_analyse_running, 0) > 0, normal_read_iops_limit * 2, normal_read_iops_limit)"
    label       = "Read IOPS limit"
    return_data = false
  }

  metric_query {
    id          = "normal_read_iops_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(read_iops, 4))"
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
    id          = "database_analyse_running"
    return_data = false

    metric {
      namespace   = aws_cloudwatch_log_metric_filter.database_analyse_in_progress.metric_transformation[0].namespace
      metric_name = aws_cloudwatch_log_metric_filter.database_analyse_in_progress.metric_transformation[0].name
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
