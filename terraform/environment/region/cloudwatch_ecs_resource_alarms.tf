locals {
  live_ecs_services = {
    front               = aws_ecs_service.front.name
    admin               = aws_ecs_service.admin.name
    api                 = aws_ecs_service.api.name
    htmltopdf           = aws_ecs_service.htmltopdf.name
    scan                = aws_ecs_service.scan.name
    "sirius-files-sync" = aws_ecs_service.sirius_files_sync.name
  }
}

resource "aws_cloudwatch_metric_alarm" "ecs_high_cpu" {
  for_each = local.live_ecs_services

  alarm_name          = "${local.environment}-${each.key}-ecs-high-cpu"
  alarm_description   = "ECS service ${each.value} has had average CPU utilisation at or above 85% for 45 minutes."
  namespace           = "AWS/ECS"
  metric_name         = "CPUUtilization"
  statistic           = "Average"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"

  dimensions = {
    ClusterName = aws_ecs_cluster.main.name
    ServiceName = each.value
  }

  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  ok_actions                = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.environment.resource_alarms_active
  tags                      = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "ecs_high_memory" {
  for_each = local.live_ecs_services

  alarm_name          = "${local.environment}-${each.key}-ecs-high-memory"
  alarm_description   = "ECS service ${each.value} has had average memory utilisation at or above 85% for 45 minutes."
  namespace           = "AWS/ECS"
  metric_name         = "MemoryUtilization"
  statistic           = "Average"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"

  dimensions = {
    ClusterName = aws_ecs_cluster.main.name
    ServiceName = each.value
  }

  alarm_actions             = [data.aws_sns_topic.alerts.arn]
  ok_actions                = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.environment.resource_alarms_active
  tags                      = var.default_tags
}
