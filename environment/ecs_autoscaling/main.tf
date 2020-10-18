resource "aws_appautoscaling_target" "ecs_service" {
  max_capacity       = var.ecs_task_autoscaling_maximum
  min_capacity       = var.ecs_task_autoscaling_minimum
  resource_id        = "service/${var.aws_ecs_cluster_name}/${var.aws_ecs_service_name}"
  role_arn           = var.ecs_autoscaling_service_role_arn
  scalable_dimension = "ecs:service:DesiredCount"
  service_namespace  = "ecs"
}

resource "aws_appautoscaling_policy" "cpu_track_metric" {
  name               = "${var.environment}-${var.aws_ecs_service_name}-cpu-target-tracking"
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.ecs_service.resource_id
  scalable_dimension = aws_appautoscaling_target.ecs_service.scalable_dimension
  service_namespace  = aws_appautoscaling_target.ecs_service.service_namespace

  target_tracking_scaling_policy_configuration {
    target_value       = var.autoscaling_metric_track_cpu_target
    scale_in_cooldown  = var.cpu_track_metric_scale_in_cooldown
    scale_out_cooldown = var.cpu_track_metric_scale_out_cooldown

    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageCPUUtilization"
    }
  }
}

resource "aws_appautoscaling_policy" "memory_track_metric" {
  name               = "${var.environment}-${var.aws_ecs_service_name}-memory-target-tracking"
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.ecs_service.resource_id
  scalable_dimension = aws_appautoscaling_target.ecs_service.scalable_dimension
  service_namespace  = aws_appautoscaling_target.ecs_service.service_namespace

  target_tracking_scaling_policy_configuration {
    target_value       = var.autoscaling_metric_track_memory_target
    scale_in_cooldown  = var.memory_track_metric_scale_in_cooldown
    scale_out_cooldown = var.memory_track_metric_scale_out_cooldown

    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageMemoryUtilization"
    }
  }
}

resource "aws_cloudwatch_metric_alarm" "max_scaling_reached" {
  alarm_name                = "${var.environment}-${var.aws_ecs_service_name}-max-scaling-reached"
  comparison_operator       = "GreaterThanOrEqualToThreshold"
  evaluation_periods        = "2"
  metric_name               = "RunningTaskCount"
  namespace                 = "ECS/ContainerInsights"
  period                    = "30"
  statistic                 = "Average"
  threshold                 = var.ecs_task_autoscaling_maximum
  alarm_description         = "This metric monitors ecs running task count for the ${var.environment} ${var.aws_ecs_service_name} service"
  insufficient_data_actions = []
  dimensions = {
    ServiceName = var.aws_ecs_service_name
    ClusterName = "${var.environment}-online-lpa"
  }
}
