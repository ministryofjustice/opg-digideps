output "task_definition_arn" {
  value       = aws_ecs_task_definition.dr_backup.arn
  description = "Cross Account Backup Task Definition ARN"
}

output "task_role_arn" {
  value       = aws_iam_role.dr_backup.arn
  description = "Cross Account Backup Task Role ARN"
}
