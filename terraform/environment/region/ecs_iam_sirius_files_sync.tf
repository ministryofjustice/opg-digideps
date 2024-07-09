resource "aws_iam_role" "sirius_files_sync" {
  assume_role_policy = data.aws_iam_policy_document.sirius_files_task_role_assume_policy.json
  name               = "sirius-files-sync.${local.environment}"
  tags               = var.default_tags
}

# ===== assume role policy (not the standard role one we use for most roles)
data "aws_iam_policy_document" "sirius_files_task_role_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }

  statement {
    sid    = "assumeIntegrationCI"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${var.account.sirius_api_account}:role/integrations-ci"
      ]
    }
    actions = ["sts:AssumeRole"]
  }
}

# ======= S3 PERMISSIONS ===== (Use same policy doc as the front container for S3)
resource "aws_iam_role_policy" "sirius_files_sync_s3" {
  name   = "sirius-files-sync-s3.${local.environment}"
  policy = data.aws_iam_policy_document.front_s3.json
  role   = aws_iam_role.sirius_files_sync.id
}

# ======= INVOKE API GATEWAY PERMISSIONS =====
resource "aws_iam_role_policy" "sirius_files_sync_invoke_api_gateway" {
  name   = "sirius-files-sync-api-gw.${local.environment}"
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
}

# ======= PARAMETER STORE PERMISSIONS ===== (needed for various flags)
resource "aws_iam_role_policy" "sirius_files_sync_query_ssm" {
  name   = "sirius-files-sync-query-ssm.${local.environment}"
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
