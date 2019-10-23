output "render" {
  value = {
    Cluster    = var.cluster_name
    LaunchType = "FARGATE"
    NetworkConfiguration = {
      AwsvpcConfiguration = {
        SecurityGroups = [aws_security_group.task.id]
        Subnets        = var.subnet_ids
      }
    }
    TaskDefinition = aws_ecs_task_definition.task.arn
  }
}

output "security_group_id" {
  value = aws_security_group.task.id
}

