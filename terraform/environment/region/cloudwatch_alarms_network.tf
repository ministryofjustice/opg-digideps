resource "aws_cloudwatch_metric_alarm" "ecs_network_tx_bytes" {
  alarm_name        = "${local.environment}-ecs-network-tx-bytes"
  alarm_description = "NetworkTxBytes for ECS cluster ${aws_ecs_cluster.main.name}."
  actions_enabled   = var.account.environment.alarms_active
  alarm_actions     = [data.aws_sns_topic.alerts.arn]
  ok_actions        = var.account.environment.is_production == 1 ? [data.aws_sns_topic.alerts.arn] : []

  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 5
  datapoints_to_alarm = 5
  threshold           = 300000
  period              = 60

  namespace   = "ECS/ContainerInsights"
  metric_name = "NetworkTxBytes"
  statistic   = "Sum"

  dimensions = {
    ClusterName = aws_ecs_cluster.main.name
  }

  treat_missing_data = "notBreaching"
  tags               = var.default_tags
}
