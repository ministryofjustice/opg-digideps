resource "aws_iam_role" "integration_tests" {
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  name               = "integration-tests.${local.environment}"
  tags               = var.default_tags
}

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

data "aws_iam_policy_document" "admin_put_parameter_ssm" {
  statement {
    sid    = "AllowPutSSMParameters"
    effect = "Allow"
    actions = [
      "ssm:PutParameter",
      "ssm:GetParameter",
      "ssm:GetParameters"
    ]
    resources = [
      aws_ssm_parameter.flag_document_sync.arn,
    ]
  }
}

resource "aws_iam_role_policy" "admin_put_parameter_ssm_integration_tests" {
  name   = "admin-put-parameter-ssm-integration-tests.${local.environment}"
  policy = data.aws_iam_policy_document.admin_put_parameter_ssm.json
  role   = aws_iam_role.integration_tests.id
}
