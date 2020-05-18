resource "aws_iam_role" "api" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "api.${local.environment}"
}

resource "aws_iam_role_policy" "api_task_logs" {
  name   = "api-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.api.id
}
