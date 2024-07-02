resource "aws_iam_role" "sirius_files_sync" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "document-sync.${local.environment}"
  tags               = var.default_tags
}

# ======= S3 PERMISSIONS ===== (Use same policy doc as the front container for S3)
resource "aws_iam_role_policy" "sirius_files_sync_s3" {
  name   = "document-sync-s3.${local.environment}"
  policy = data.aws_iam_policy_document.front_s3.json
  role   = aws_iam_role.sirius_files_sync.id
}

# ======= INVOKE API GATEWAY PERMISSIONS =====
resource "aws_iam_role_policy" "sirius_files_sync_invoke_api_gateway" {
  name   = "document-sync-api-gw.${local.environment}"
  policy = data.aws_iam_policy_document.sirius_files_sync_invoke_api_gateway.json
  role   = aws_iam_role.sirius_files_sync.id
}

data "aws_iam_policy_document" "sirius_files_sync_invoke_api_gateway" {
  statement {
    sid    = "AllowInvokeOnDeputyReportingGateway"
    effect = "Allow"
    actions = [
      "execute-api:Invoke",
      "execute-api:ManageConnections"
    ]
    resources = ["arn:aws:execute-api:eu-west-1:${var.account.sirius_api_account}:*"]
  }

  statement {
    sid       = "allowAssumeAccess"
    effect    = "Allow"
    resources = ["arn:aws:iam::${var.account.sirius_api_account}:role/integrations-ci"]
    actions = [
      "sts:AssumeRole"
    ]
  }
}

# ======= PARAMETER STORE PERMISSIONS ===== (needed for various flags)
resource "aws_iam_role_policy" "sirius_files_sync_query_ssm" {
  name   = "document-sync-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.sirius_files_sync_query_ssm.json
  role   = aws_iam_role.sirius_files_sync.id
}

data "aws_iam_policy_document" "sirius_files_sync_query_ssm" {
  statement {
    sid    = "AllowQuerySSMParameters"
    effect = "Allow"
    actions = [
      "ssm:GetParameter"
    ]
    resources = [
      aws_ssm_parameter.checklist_sync_row_limit.arn,
      aws_ssm_parameter.document_sync_row_limit.arn,
      aws_ssm_parameter.flag_checklist_sync.arn,
      aws_ssm_parameter.flag_document_sync.arn,
      aws_ssm_parameter.flag_paper_reports.arn
    ]
  }
}

# ======= SECRETS PERMISSIONS =====
#resource "aws_iam_role_policy" "document-sync_query_secretsmanager" {
#  name   = "document-sync-query-secretsmanager.${local.environment}"
#  policy = data.aws_iam_policy_document.sirius_files_sync_query_secretsmanager.json
#  role   = aws_iam_role.sirius_files_sync.id
#}
#
#data "aws_iam_policy_document" "sirius_files_sync_query_secretsmanager" {
#  statement {
#    sid    = "AllowQuerySecretsmanagerSecrets"
#    effect = "Allow"
#    actions = [
#      "secretsmanager:GetSecretValue"
#    ]
#    resources = [
#      data.aws_secretsmanager_secret.database_password.arn,
#      data.aws_secretsmanager_secret_version.database_password.arn,
#      data.aws_secretsmanager_secret.api_secret.arn,
#      data.aws_secretsmanager_secret.front_frontend_secret.arn,
#      data.aws_secretsmanager_secret.admin_frontend_secret.arn,
#      data.aws_secretsmanager_secret.admin_api_client_secret.arn,
#      data.aws_secretsmanager_secret.front_api_client_secret.arn,
#      data.aws_secretsmanager_secret.front_notify_api_key.arn,
#    ]
#  }
#}

#resource "aws_iam_role_policy" "sirius_files_sync_get_log_events" {
#  name   = "document-sync-get-log-events.${local.environment}"
#  policy = data.aws_iam_policy_document.sirius_files_sync_get_log_events.json
#  role   = aws_iam_role.sirius_files_sync.id
#}
#
#data "aws_iam_policy_document" "sirius_files_sync_get_log_events" {
#  statement {
#    sid    = "AllowGetLogEvents"
#    effect = "Allow"
#    actions = [
#      "logs:GetLogEvents"
#    ]
#    resources = [aws_cloudwatch_log_group.opg_digi_deps.arn]
#  }
#}

#resource "aws_iam_role_policy" "document-sync_task_logs" {
#  name   = "document-sync-task-logs.${local.environment}"
#  policy = data.aws_iam_policy_document.ecs_task_logs.json
#  role   = aws_iam_role.sirius_files_sync.id
#}
