resource "aws_iam_role" "front" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "front.${local.environment}"
  tags               = local.default_tags
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
      "s3:ListObjectVersions",
      "s3:ListBucketVersions",
      "s3:PutObject",
      "s3:GetObjectTagging",
      "s3:PutObjectTagging",
    ]
    resources = [
      "${aws_s3_bucket.pa_uploads.arn}",
      "${aws_s3_bucket.pa_uploads.arn}/*",
    ]
  }
}

resource "aws_iam_role_policy" "invoke_dep_rep_api" {
  name   = "front-dep-rep-api.${local.environment}"
  policy = data.aws_iam_policy_document.invoke_dep_rep_api.json
  role   = aws_iam_role.front.id
}

data "aws_iam_policy_document" "invoke_dep_rep_api" {
  statement {
    sid    = "AllowInvokeOnDeputyReportingGateway"
    effect = "Allow"
    actions = [
      "execute-api:Invoke",
      "execute-api:ManageConnections"
    ]
    resources = ["arn:aws:execute-api:eu-west-1:${local.account.sirius_api_account}:*"]
  }
}

resource "aws_iam_role_policy" "query_ssm" {
  name   = "front-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.query_ssm.json
  role   = aws_iam_role.front.id
}

data "aws_iam_policy_document" "query_ssm" {
  statement {
    sid    = "AllowQuerySSMParameters"
    effect = "Allow"
    actions = [
      "ssm:GetParameter"
    ]
    resources = [
      aws_ssm_parameter.flag_document_sync.arn,
      aws_ssm_parameter.document_sync_interval_minutes.arn,
      aws_ssm_parameter.document_sync_row_limit.arn
    ]
  }
}
