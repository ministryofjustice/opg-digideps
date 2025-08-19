resource "aws_iam_role" "api" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "api.${local.environment}"
  tags               = var.default_tags
}

resource "aws_iam_role_policy" "api_task_logs" {
  name   = "api-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.api.id
}

resource "aws_iam_role_policy" "api_query_ssm" {
  name   = "api-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.api_permissions.json
  role   = aws_iam_role.api.id
}

data "aws_iam_policy_document" "api_permissions" {
  statement {
    sid    = "AllowQuerySSMParameters"
    effect = "Allow"
    actions = [
      "ssm:GetParameter"
    ]
    resources = [
      aws_ssm_parameter.flag_document_sync.arn,
      aws_ssm_parameter.document_sync_row_limit.arn,
      aws_ssm_parameter.flag_checklist_sync.arn,
      aws_ssm_parameter.checklist_sync_row_limit.arn
    ]
  }

  statement {
    sid    = "AllowQuerySecretsManager"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue"
    ]
    resources = [
      data.aws_secretsmanager_secret.private_jwt_key_base64.arn,
      data.aws_secretsmanager_secret.public_jwt_key_base64.arn,
      data.aws_secretsmanager_secret.database_password.arn,
      data.aws_secretsmanager_secret.application_db_password.arn
    ]
  }

  statement {
    sid    = "DecryptSecretKMS"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      data.aws_kms_alias.cloudwatch_application_secret_encryption.target_key_arn
    ]
  }

  statement {
    sid    = "ApiGetSiriusS3Bucket"
    effect = "Allow"
    actions = [
      "s3:GetObject",
    ]
    resources = [
      "arn:aws:s3:::digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk",
      "arn:aws:s3:::digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk/*"
    ]
  }

  statement {
    sid    = "ApiKMSSiriusS3Decrypt"
    effect = "Allow"
    actions = [
      "kms:Decrypt",
    ]
    resources = [
      "arn:aws:kms:eu-west-1:${var.account.sirius_api_account}:key/*"
    ]
  }
}
