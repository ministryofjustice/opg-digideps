data "aws_iam_policy_document" "task_role_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

data "aws_iam_policy_document" "ecs_task_logs" {
  statement {
    effect = "Allow"
    #trivy:ignore:avd-aws-0057 - Describe only so not overly permissive given role
    resources = ["arn:aws:logs:*:*:*"]
    actions = [
      "logs:DescribeLogGroups",
      "logs:DescribeLogStreams"
    ]
  }

  statement {
    effect = "Allow"
    resources = [
      "${aws_cloudwatch_log_group.audit.arn}:log-stream:*",
      aws_cloudwatch_log_group.audit.arn
    ]
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents"
    ]
  }
}

data "aws_iam_role" "ecs_autoscaling_service_role" {
  name = "AWSServiceRoleForApplicationAutoScaling_ECSService"
}
