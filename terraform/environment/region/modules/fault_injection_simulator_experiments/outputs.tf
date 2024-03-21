output "ecs_stop_frontend_tasks_template_id" {
  value = aws_fis_experiment_template.ecs_stop_frontend_tasks.id
}

output "ecs_front_cpu_stress_template_id" {
  value = aws_fis_experiment_template.ecs_front_cpu_stress.id
}

output "front_io_stress_template_id" {
  value = aws_fis_experiment_template.ecs_front_io_stress.id
}
