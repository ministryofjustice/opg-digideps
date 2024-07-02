resource "aws_iam_role" "front" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "front.${local.environment}"
  tags               = var.default_tags
}

resource "aws_iam_role_policy" "front_s3" {
  name   = "front-s3.${local.environment}"
  policy = data.aws_iam_policy_document.front_s3.json
  role   = aws_iam_role.front.id
}

data "aws_iam_policy_document" "front_s3" {
  statement {
    sid    = "AllFrontActionsCalledOnS3Bucket"
    effect = "Allow"
    actions = [
      "s3:GetObject",
      "s3:DeleteObject",
      "s3:DeleteObjectVersion",
      "s3:ListBucketVersions",
      "s3:PutObject",
      "s3:GetObjectTagging",
      "s3:PutObjectTagging",
      "s3:ListBucket"
    ]
    #tfsec:ignore:aws-iam-no-policy-wildcards - Not overly permissive
    resources = [
      module.pa_uploads.arn,
      "${module.pa_uploads.arn}/*",
    ]
  }
}

resource "aws_iam_role_policy" "front_query_secretsmanager" {
  name   = "front-query-secretsmanager.${local.environment}"
  policy = data.aws_iam_policy_document.front_query_secretsmanager.json
  role   = aws_iam_role.front.id
}

data "aws_iam_policy_document" "front_query_secretsmanager" {
  statement {
    sid    = "AllowQuerySecretsmanagerSecrets"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue"
    ]
    resources = [
      data.aws_secretsmanager_secret.database_password.arn,
      data.aws_secretsmanager_secret_version.database_password.arn,
      data.aws_secretsmanager_secret.api_secret.arn,
      data.aws_secretsmanager_secret.front_frontend_secret.arn,
      data.aws_secretsmanager_secret.admin_frontend_secret.arn,
      data.aws_secretsmanager_secret.admin_api_client_secret.arn,
      data.aws_secretsmanager_secret.front_api_client_secret.arn,
      data.aws_secretsmanager_secret.front_notify_api_key.arn,
    ]
  }
}

resource "aws_iam_role_policy" "front_get_log_events" {
  name   = "front-get-log-events.${local.environment}"
  policy = data.aws_iam_policy_document.front_get_log_events.json
  role   = aws_iam_role.front.id
}

data "aws_iam_policy_document" "front_get_log_events" {
  statement {
    sid    = "AllowGetLogEvents"
    effect = "Allow"
    actions = [
      "logs:GetLogEvents"
    ]
    resources = [aws_cloudwatch_log_group.opg_digi_deps.arn]
  }
}

resource "aws_iam_role_policy" "front_task_logs" {
  name   = "front-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.front.id
}
