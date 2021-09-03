resource "aws_appautoscaling_target" "target" {
  service_namespace  = "ecs"
  resource_id        = "service/${var.aws_ecs_cluster_name}/${var.aws_ecs_service_name}"
  scalable_dimension = "ecs:service:DesiredCount"
  role_arn           = var.ecs_autoscaling_service_role_arn
  max_capacity       = var.ecs_task_autoscaling_maximum
  min_capacity       = var.ecs_task_autoscaling_minimum
}

# Automatically scale capacity up by one
resource "aws_appautoscaling_policy" "up" {
  name               = "${var.environment}-${var.aws_ecs_service_name}-scale-up"
  service_namespace  = "ecs"
  resource_id        = "service/${var.aws_ecs_cluster_name}/${var.aws_ecs_service_name}"
  scalable_dimension = "ecs:service:DesiredCount"

  step_scaling_policy_configuration {
    adjustment_type         = "ChangeInCapacity"
    cooldown                = 60
    metric_aggregation_type = "Maximum"

    step_adjustment {
      metric_interval_lower_bound = 0
      scaling_adjustment          = 1
    }
  }

  depends_on = [aws_appautoscaling_target.target]
}

# Automatically scale capacity down by one
resource "aws_appautoscaling_policy" "down" {
  name               = "${var.environment}-${var.aws_ecs_service_name}-scale-down"
  service_namespace  = "ecs"
  resource_id        = "service/${var.aws_ecs_cluster_name}/${var.aws_ecs_service_name}"
  scalable_dimension = "ecs:service:DesiredCount"

  step_scaling_policy_configuration {
    adjustment_type         = "ChangeInCapacity"
    cooldown                = 600
    metric_aggregation_type = "Maximum"

    step_adjustment {
      metric_interval_lower_bound = 0
      scaling_adjustment          = -1
    }
  }

  depends_on = [aws_appautoscaling_target.target]
}

# Use bespoke metrics for two reasons.
# 1) so we dont wobble between cpu and memory scaling
# 2) we can turn off alarms at min scaling

resource "aws_cloudwatch_metric_alarm" "scale_up" {
  alarm_name                = "${var.environment}-${var.aws_ecs_service_name}-scale-up"
  comparison_operator       = "GreaterThanOrEqualToThreshold"
  evaluation_periods        = "2"
  threshold                 = "1"
  alarm_description         = "Scale up based on Mem, Cpu and Task Count"
  insufficient_data_actions = []

  metric_query {
    id          = "up"
    expression  = "IF((cpu > 80 OR mem > 80) AND tc < ${var.ecs_task_autoscaling_maximum}, 1, 0)"
    label       = "ContainerScaleUp"
    return_data = "true"
  }

  metric_query {
    id = "cpu"

    metric {
      metric_name = "CPUUtilization"
      namespace   = "AWS/ECS"
      period      = "60"
      stat        = "Average"

      dimensions = {
        ServiceName = var.aws_ecs_service_name
        ClusterName = var.aws_ecs_cluster_name
      }
    }
  }

  metric_query {
    id = "mem"

    metric {
      metric_name = "MemoryUtilization"
      namespace   = "AWS/ECS"
      period      = "60"
      stat        = "Average"

      dimensions = {
        ServiceName = var.aws_ecs_service_name
        ClusterName = var.aws_ecs_cluster_name
      }
    }
  }

  metric_query {
    id = "tc"

    metric {
      metric_name = "DesiredTaskCount"
      namespace   = "ECS/ContainerInsights"
      period      = "60"
      stat        = "Average"
      unit        = "Count"

      dimensions = {
        ServiceName = var.aws_ecs_service_name
        ClusterName = var.aws_ecs_cluster_name
      }
    }
  }
  alarm_actions = [aws_appautoscaling_policy.up.arn]
}

resource "aws_cloudwatch_metric_alarm" "scale_down" {
  alarm_name                = "${var.environment}-${var.aws_ecs_service_name}-scale-down"
  comparison_operator       = "GreaterThanOrEqualToThreshold"
  evaluation_periods        = "2"
  threshold                 = "1"
  alarm_description         = "Scale down based on Mem, Cpu and Task Count"
  insufficient_data_actions = []

  metric_query {
    id          = "down"
    expression  = "IF((cpu < 30 AND mem < 40) AND tc > ${var.ecs_task_autoscaling_minimum}, 1, 0)"
    label       = "ContainerScaleUp"
    return_data = "true"
  }

  metric_query {
    id = "cpu"

    metric {
      metric_name = "CPUUtilization"
      namespace   = "AWS/ECS"
      period      = "60"
      stat        = "Average"

      dimensions = {
        ServiceName = var.aws_ecs_service_name
        ClusterName = var.aws_ecs_cluster_name
      }
    }
  }

  metric_query {
    id = "mem"

    metric {
      metric_name = "MemoryUtilization"
      namespace   = "AWS/ECS"
      period      = "60"
      stat        = "Average"

      dimensions = {
        ServiceName = var.aws_ecs_service_name
        ClusterName = var.aws_ecs_cluster_name
      }
    }
  }

  metric_query {
    id = "tc"

    metric {
      metric_name = "DesiredTaskCount"
      namespace   = "ECS/ContainerInsights"
      period      = "60"
      stat        = "Average"
      unit        = "Count"

      dimensions = {
        ServiceName = var.aws_ecs_service_name
        ClusterName = var.aws_ecs_cluster_name
      }
    }
  }
  alarm_actions = [aws_appautoscaling_policy.down.arn]
}
