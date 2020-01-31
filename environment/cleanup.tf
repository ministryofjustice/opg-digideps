data "aws_iam_role" "events_task_runner" {
  name = "events_task_runner"
}

resource "aws_cloudwatch_event_target" "cleanup" {
  rule     = "nightly" # There's no aws_cloudwatch_event_rule data source, so hard-code name
  arn      = aws_ecs_cluster.main.arn
  role_arn = data.aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
    }
  }

  input = <<DOC
{
  "containerOverrides": [
    {
      "name": "api_app",
      "command": [ "sh", "cleanup.sh" ]
    }
  ]
}
DOC
}
