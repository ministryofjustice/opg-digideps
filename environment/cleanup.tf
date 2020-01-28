resource "aws_cloudwatch_event_target" "cleanup" {
  rule     = aws_cloudwatch_event_rule.nightly.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.execution_role.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
  }

  input = <<DOC
{
  "containerOverrides": [
    {
      "name": "api",
      "command": [ "sh", "scripts/cleanup.sh" ]
    }
  ]
}
DOC
}
