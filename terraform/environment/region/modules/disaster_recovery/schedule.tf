resource "aws_cloudwatch_event_rule" "dr_backup" {
  name                = "backup-cross-account-${terraform.workspace}"
  description         = "Execute the cross account DR backup for ${terraform.workspace}"
  schedule_expression = "cron(00 01 * * ? *)"
  state               = "ENABLED"
}

resource "aws_cloudwatch_event_target" "dr_backup" {
  target_id = "backup-cross-account-${terraform.workspace}"
  arn       = var.aws_ecs_cluster_arn
  rule      = aws_cloudwatch_event_rule.dr_backup.name
  role_arn  = var.task_runner_arn

  ecs_target {
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.dr_backup.arn
    network_configuration {
      subnets          = var.aws_subnet_ids
      assign_public_ip = false
      security_groups  = [module.dr_backup_security_group.id]
    }
  }
}
