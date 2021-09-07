output "render" {
  value = {
    Cluster    = var.cluster_name
    LaunchType = "FARGATE"
    NetworkConfiguration = {
      AwsvpcConfiguration = {
        SecurityGroups = [var.security_group_id]
        Subnets        = var.subnet_ids
      }
    }
    TaskDefinition = aws_ecs_task_definition.task.arn
  }
}

output "render_with_override" {
  value = {
    Cluster    = var.cluster_name
    LaunchType = "FARGATE"
    NetworkConfiguration = {
      AwsvpcConfiguration = {
        SecurityGroups = [var.security_group_id]
        Subnets        = var.subnet_ids
      }
    }
    TaskDefinition = aws_ecs_task_definition.task.arn
    Overrides : {
      ContainerOverrides : [{
        Name : var.service_name,
        Command : var.override
      }]
    }
  }
}

output "security_group_id" {
  value = var.security_group_id
}

output "name" {
  value = var.name
}

output "task_definition_arn" {
  value = aws_ecs_task_definition.task.arn
}
