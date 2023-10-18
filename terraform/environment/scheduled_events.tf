# Delete inactive users

resource "aws_cloudwatch_event_rule" "delete_inactive_users" {
  name                = "delete-inactive-users-${local.environment}"
  description         = "Delete inactive admin users"
  schedule_expression = "cron(0 3 ? * 1 *)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "delete_inactive_users" {
  rule     = aws_cloudwatch_event_rule.delete_inactive_users.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
    }
  }

  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:delete-inactive-users"]
        }
      ]
    }
  )
}

# Delete zero activity users
resource "aws_cloudwatch_event_rule" "delete_zero_activity_users" {
  name                = "delete-zero-activity-users-${local.environment}"
  description         = "Delete zero activity users"
  schedule_expression = "cron(10 3 * * ? *)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "delete_zero_activity_users" {
  rule     = aws_cloudwatch_event_rule.delete_zero_activity_users.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:delete-zero-activity-users"]
        }
      ]
    }
  )
}
