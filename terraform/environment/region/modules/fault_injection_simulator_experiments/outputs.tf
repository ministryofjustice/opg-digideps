output "ecs_stop_frontend_tasks_template_id" {
  description = "task stop template id"
  value       = aws_fis_experiment_template.ecs_stop_frontend_tasks.id
}

output "ecs_front_cpu_stress_template_id" {
  description = "cpu stress template id"
  value       = aws_fis_experiment_template.ecs_front_cpu_stress.id
}

output "front_io_stress_template_id" {
  description = "io stress template id"
  value       = aws_fis_experiment_template.ecs_front_io_stress.id
}
