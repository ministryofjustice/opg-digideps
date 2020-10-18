variable "aws_ecs_cluster_name" {
  description = "Name of the ECS cluster for the service being scaled."
  type        = string
}

variable "aws_ecs_service_name" {
  description = "Name of the ECS service."
  type        = string
}

variable "ecs_autoscaling_service_role_arn" {
  description = "The ARN of the IAM role that allows Application AutoScaling to modify your scalable target on your behalf."
  type        = string
}

variable "environment" {
  description = "Name of the environment instance of the online LPA service."
  type        = string
}

variable "ecs_task_autoscaling_maximum" {
  description = "The max capacity of the scalable target."
  type        = number
}

variable "autoscaling_metric_track_cpu_target" {
  description = "The target value for the CPU metric."
  type        = number
  default     = 80
}

variable "autoscaling_metric_track_memory_target" {
  description = "The target value for the memory metric."
  type        = number
  default     = 80
}

variable "ecs_task_autoscaling_minimum" {
  description = "The min capacity of the scalable target."
  type        = number
  default     = 1
}

variable "memory_track_metric_scale_in_cooldown" {
  description = "The amount of time, in seconds, after a scale in activity completes before another scale in activity can start."
  type        = number
  default     = 60
}

variable "memory_track_metric_scale_out_cooldown" {
  description = "The amount of time, in seconds, after a scale out activity completes before another scale out activity can start."
  type        = number
  default     = 60
}

variable "cpu_track_metric_scale_in_cooldown" {
  description = "The amount of time, in seconds, after a scale in activity completes before another scale in activity can start."
  type        = number
  default     = 60
}

variable "cpu_track_metric_scale_out_cooldown" {
  description = "The amount of time, in seconds, after a scale out activity completes before another scale out activity can start."
  type        = number
  default     = 60
}
