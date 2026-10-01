resource "aws_cloudwatch_metric_alarm" "aurora_high_cpu" {
  for_each = module.database[0].instance_identifiers

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
  for_each = module.database[0].instance_identifiers

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
