resource "aws_ecs_cluster" "main" {
  name = "${terraform.workspace}"
  tags = "${local.default_tags}"
}

#TODO: remove ec2 once instances removed
data "aws_iam_policy_document" "task_role_assume_policy" {
  "statement" {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com", "ec2.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role" "execution_role" {
  name               = "execution_role.${terraform.workspace}"
  assume_role_policy = "${data.aws_iam_policy_document.execution_role_assume_policy.json}"
  tags               = "${local.default_tags}"
}

data "aws_iam_policy_document" "execution_role_assume_policy" {
  "statement" {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "execution_role" {
  policy = "${data.aws_iam_policy_document.execution_role.json}"
  role   = "${aws_iam_role.execution_role.id}"
}

resource "aws_cloudwatch_log_group" "opg_digi_deps" {
  name = "${terraform.workspace}"
  tags = "${local.default_tags}"
}

data "aws_iam_policy_document" "execution_role" {
  "statement" {
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "ecr:GetAuthorizationToken",
      "ecr:BatchCheckLayerAvailability",
      "ecr:GetDownloadUrlForLayer",
      "ecr:BatchGetImage",
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "ssm:GetParameters",
      "secretsmanager:GetSecretValue",
    ]
  }

  "statement" {
    effect = "Allow"

    actions = [
      "kms:Decrypt",
    ]

    resources = [
      "${data.aws_kms_alias.secretmanager.target_key_arn}",
    ]
  }
}

resource "aws_service_discovery_private_dns_namespace" "private" {
  name = "${terraform.workspace}.private"
  vpc  = "${data.aws_vpc.vpc.id}"
}
