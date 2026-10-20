locals {
  redis_replication_groups = {
    api      = aws_elasticache_replication_group.elasticache_api.replication_group_id
    frontend = aws_elasticache_replication_group.elasticache_front.replication_group_id
  }
}

resource "aws_cloudwatch_metric_alarm" "redis_high_cpu" {
  for_each = local.redis_replication_groups

  alarm_name          = "${var.account.name}-${each.key}-redis-high-cpu"
  alarm_description   = "Redis replication group ${each.value} has had average engine CPU utilisation at or above 90% for 45 minutes."
  namespace           = "AWS/ElastiCache"
  metric_name         = "EngineCPUUtilization"
  statistic           = "Average"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 90
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"

  dimensions = {
    ReplicationGroupId = each.value
    Role               = "Primary"
  }

  alarm_actions             = [aws_sns_topic.alerts.arn]
  ok_actions                = var.account.pagerduty_enabled ? [aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.resource_alarms_active
  tags                      = var.default_tags
}

resource "aws_cloudwatch_metric_alarm" "redis_high_evictions" {
  for_each = local.redis_replication_groups

  alarm_name          = "${var.account.name}-${each.key}-redis-high-evictions"
  alarm_description   = "Redis replication group ${each.value} has experienced unusually high key evictions for 15 minutes."
  comparison_operator = "GreaterThanUpperThreshold"
  evaluation_periods  = 3
  datapoints_to_alarm = 3
  threshold_metric_id = "expected_evictions"
  treat_missing_data  = "notBreaching"

  metric_query {
    id          = "expected_evictions"
    expression  = "ANOMALY_DETECTION_BAND(evictions, 4)"
    label       = "Expected eviction range"
    return_data = true
  }

  metric_query {
    id          = "evictions"
    return_data = true

    metric {
      namespace   = "AWS/ElastiCache"
      metric_name = "Evictions"
      period      = 300
      stat        = "Sum"

      dimensions = {
        ReplicationGroupId = each.value
        Role               = "Primary"
      }
    }
  }

  alarm_actions             = [aws_sns_topic.alerts.arn]
  ok_actions                = var.account.pagerduty_enabled ? [aws_sns_topic.alerts.arn] : []
  insufficient_data_actions = []
  actions_enabled           = var.account.resource_alarms_active
  tags                      = var.default_tags
}
