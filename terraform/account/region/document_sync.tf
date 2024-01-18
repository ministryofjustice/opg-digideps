locals {
  sirius_root       = "arn:aws:iam::${var.account.sirius_account_id}:root"
  sirius_env_lambda = "arn:aws:iam::${var.account.sirius_account_id}:role/deputy-reporting-${var.account.name}-v2"
}

resource "aws_iam_role" "integrations_s3_read" {
  assume_role_policy = data.aws_iam_policy_document.integrations_assume_role.json
  name               = "integrations-s3-read-${var.account.name}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "integrations_assume_role" {
  statement {
    sid    = "AllowIntegrationsLambdaS3"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = var.account.name == "development" ? [local.sirius_root] : [local.sirius_env_lambda]
    }
    actions = ["sts:AssumeRole"]
  }
}


data "aws_iam_policy_document" "integrations_s3_read" {
  statement {
    sid       = "AllowIntegrationsLambdaS3"
    effect    = "Allow"
    resources = var.account.name == "development" ? ["arn:aws:s3:::pa-uploads-*"] : ["arn:aws:s3:::pa-uploads-${local.s3_bucket}", "arn:aws:s3:::pa-uploads-${local.s3_bucket}/*"]
    actions   = ["s3:GetObject", "s3:ListBucket"]
  }
}

resource "aws_iam_policy" "integrations_s3_read" {
  name   = "integration-s3-read-${var.account.name}"
  policy = data.aws_iam_policy_document.integrations_s3_read.json
}

resource "aws_iam_role_policy_attachment" "access_policy_attachment" {
  role       = aws_iam_role.integrations_s3_read.id
  policy_arn = aws_iam_policy.integrations_s3_read.arn
}
