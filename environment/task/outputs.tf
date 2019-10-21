output "render" {
  value = {
    Cluster = var.cluster_name
    LaunchType = "FARGATE"
    NetworkConfiguration = {
      AwsvpcConfiguration = {
        SecurityGroups = [aws_security_group.task.id]
        Subnets = var.subnets
      }
    }
    TaskDefinition = aws_ecs_task_definition.task.arn
  }
}


