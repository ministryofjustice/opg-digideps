output "task_definition_arn" {
  value       = aws_ecs_task_definition.dr_backup.arn
  description = "DR task definition ARN"
}

output "task_role_arn" {
  value       = aws_iam_role.dr_backup.arn
  description = "DR task role ARN"
}
