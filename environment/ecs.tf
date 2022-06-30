resource "aws_ecs_cluster" "main" {
  name = local.environment
  tags = local.default_tags
}

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

resource "aws_iam_role" "execution_role" {
  name               = "execution_role.${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.execution_role_assume_policy.json
  tags               = local.default_tags
}

data "aws_iam_policy_document" "execution_role_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "execution_role" {
  policy = data.aws_iam_policy_document.execution_role.json
  role   = aws_iam_role.execution_role.id
}

data "aws_iam_policy_document" "execution_role" {
  statement {
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "ecr:GetAuthorizationToken",
      "ecr:BatchCheckLayerAvailability",
      "ecr:GetDownloadUrlForLayer",
      "ecr:BatchGetImage",
      "logs:CreateLogStream",
      "logs:GetLogEvents",
      "logs:PutLogEvents",
      "ssm:GetParameters",
      "secretsmanager:GetSecretValue",
    ]
  }

  statement {
    effect = "Allow"

    actions = [
      "kms:Decrypt",
    ]

    resources = [
      data.aws_kms_alias.secretmanager.target_key_arn,
    ]
  }
}

resource "aws_service_discovery_private_dns_namespace" "private" {
  name = "${local.environment}.private"
  vpc  = data.aws_vpc.vpc.id
  tags = local.default_tags
}

data "aws_iam_policy_document" "ecs_task_logs" {
  statement {
    effect    = "Allow"
    resources = ["*"]

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
