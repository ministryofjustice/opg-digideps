# Generic task runner role that has limited permissions
resource "aws_iam_role" "task_runner" {
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  name               = "task-runner.${local.environment}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "ecs_task_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}
