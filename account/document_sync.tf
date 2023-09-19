locals {
  sirius_root       = "arn:aws:iam::${local.account.sirius_account_id}:root"
  sirius_env_lambda = "arn:aws:iam::${local.account.sirius_account_id}:role/deputy-reporting-${local.account.name}-v2"
}

resource "aws_iam_role" "integrations_s3_read" {
  assume_role_policy = data.aws_iam_policy_document.integrations_assume_role.json
  name               = "integrations-s3-read-${local.account.name}"
  tags               = local.default_tags
}

data "aws_iam_policy_document" "integrations_assume_role" {
  statement {
    sid    = "AllowIntegrationsLambdaS3"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = local.account.name == "development" ? [local.sirius_root] : [local.sirius_env_lambda]
    }
    actions = ["sts:AssumeRole"]
  }
}


data "aws_iam_policy_document" "integrations_s3_read" {
  statement {
    sid       = "AllowIntegrationsLambdaS3"
    effect    = "Allow"
    resources = local.account.name == "development" ? ["arn:aws:s3:::pa-uploads-*"] : ["arn:aws:s3:::pa-uploads-${local.s3_bucket}", "arn:aws:s3:::pa-uploads-${local.s3_bucket}/*"]
    actions   = ["s3:GetObject", "s3:ListBucket"]
  }
}

resource "aws_iam_policy" "integrations_s3_read" {
  name   = "integration-s3-read-${local.account.name}"
  policy = data.aws_iam_policy_document.integrations_s3_read.json
}

resource "aws_iam_role_policy_attachment" "access_policy_attachment" {
  role       = aws_iam_role.integrations_s3_read.id
  policy_arn = aws_iam_policy.integrations_s3_read.arn
}
