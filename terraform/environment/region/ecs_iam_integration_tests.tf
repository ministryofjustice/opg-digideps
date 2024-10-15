resource "aws_iam_role" "integration_tests" {
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  name               = "integration-tests.${local.environment}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "integration_tests" {
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
      aws_ssm_parameter.flag_multi_accounts.arn
    ]
  }

  statement {
    sid    = "AllowS3OnSiriusBucket"
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
    #trivy:ignore:avd-aws-0057 - Not overly permissive
    resources = [
      "arn:aws:s3:::digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk",
      "arn:aws:s3:::digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk/*",
    ]
  }

  statement {
    sid    = "AllowS3OnPAUploadsBucket"
    effect = "Allow"
    actions = [
      "s3:PutObject",
      "s3:ListObjects",
      "s3:DeleteObject",
      "s3:ListBucket"
    ]
    #trivy:ignore:avd-aws-0057 - Not overly permissive
    resources = [
      "${module.pa_uploads.arn}/*",
      module.pa_uploads.arn
    ]
  }
}

resource "aws_iam_role_policy" "admin_put_parameter_ssm_integration_tests" {
  name   = "admin-put-parameter-ssm-integration-tests.${local.environment}"
  policy = data.aws_iam_policy_document.integration_tests.json
  role   = aws_iam_role.integration_tests.id
}
