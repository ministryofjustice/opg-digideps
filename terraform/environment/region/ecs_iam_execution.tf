# Shared IAM for ECS task execution role
resource "aws_iam_role" "execution_role" {
  name               = "execution_role.${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.execution_role_assume_policy.json
  tags               = var.default_tags
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
    sid       = "AllowECRTokenAccess"
    effect    = "Allow"
    resources = ["*"]
    actions   = ["ecr:GetAuthorizationToken"]
  }

  statement {
    sid    = "AllowECRAccess"
    effect = "Allow"
    resources = [
      data.aws_ecr_repository.images["api"].arn,
      data.aws_ecr_repository.images["api-webserver"].arn,
      data.aws_ecr_repository.images["client"].arn,
      data.aws_ecr_repository.images["client-webserver"].arn,
      data.aws_ecr_repository.images["sync"].arn,
      data.aws_ecr_repository.images["htmltopdf"].arn,
      data.aws_ecr_repository.images["dr-backup"].arn,
      data.aws_ecr_repository.images["file-scanner"].arn
    ]
    actions = [
      "ecr:BatchCheckLayerAvailability",
      "ecr:GetDownloadUrlForLayer",
      "ecr:BatchGetImage"
    ]
  }

  statement {
    sid    = "AllowLogsAccess"
    effect = "Allow"
    #tfsec:ignore:aws-iam-no-policy-wildcards - Required for execution role to function
    resources = ["arn:aws:logs:*:*:*"]
    actions = [
      "logs:CreateLogStream",
      "logs:GetLogEvents",
      "logs:PutLogEvents"
    ]
  }

  statement {
    sid    = "AllowSSMAccess"
    effect = "Allow"
    #tfsec:ignore:aws-iam-no-policy-wildcards - Required for execution role to function
    resources = ["arn:aws:ssm:*:*:*"]
    actions = [
      "ssm:GetParameters"
    ]
  }

  statement {
    sid    = "AllowSecretsAccess"
    effect = "Allow"
    resources = [
      data.aws_secretsmanager_secret.public_jwt_key_base64.arn,
      data.aws_secretsmanager_secret.private_jwt_key_base64.arn,
      data.aws_secretsmanager_secret.jwt_token_synchronisation.arn,
      data.aws_secretsmanager_secret.front_notify_api_key.arn,
      data.aws_secretsmanager_secret.front_frontend_secret.arn,
      data.aws_secretsmanager_secret.front_api_client_secret.arn,
      data.aws_secretsmanager_secret.database_password.arn,
      data.aws_secretsmanager_secret.custom_sql_db_password.arn,
      data.aws_secretsmanager_secret.api_secret.arn,
      data.aws_secretsmanager_secret.admin_frontend_secret.arn,
      data.aws_secretsmanager_secret.admin_api_client_secret.arn
    ]
    actions = ["secretsmanager:GetSecretValue"]
  }

  statement {
    effect  = "Allow"
    actions = ["kms:Decrypt"]
    resources = [
      data.aws_kms_alias.secretmanager.target_key_arn,
    ]
  }
}

data "aws_iam_policy_document" "ecs_task_logs" {
  statement {
    effect = "Allow"
    #tfsec:ignore:aws-iam-no-policy-wildcards - Describe only so not overly permissive given role
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
