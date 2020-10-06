resource "aws_cloudwatch_event_rule" "dr_backup" {
  name                = "Casrec-Document-Export-${terraform.workspace}"
  description         = "Execute the Casrec Document Export for ${terraform.workspace}"
  schedule_expression = "cron(00 08 * * ? *)"
}

resource "aws_cloudwatch_event_target" "dr_backup" {
  target_id = "dr-backup-${terraform.workspace}"
  arn       = aws_ecs_cluster.main.arn
  rule      = aws_cloudwatch_event_rule.dr_backup.name
  role_arn  = aws_iam_role.dr_backup.arn

  ecs_target {
    launch_type         = "FARGATE"
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.dr_backup.arn
    network_configuration {
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
      security_groups  = [module.document_sync_service_security_group.id]
    }
  }

  input = local.dr_backup_overrides
}

locals {
  dr_backup_overrides = jsonencode({
    containerOverrides = [{
      name    = "dr-backup",
      command = ["python3", "dr_backup.py"]
    }]
  })
}
