resource "aws_iam_role" "sync" {
  assume_role_policy = data.aws_iam_policy_document.sync_assume_policy.json
  name               = "sync"
  tags               = local.default_tags
}

data "aws_iam_policy_document" "sync_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "sync" {
  name   = "sync"
  policy = data.aws_iam_policy_document.sync.json
  role   = aws_iam_role.sync.id
}

data "aws_iam_policy_document" "sync" {
  statement {
    sid     = "AllowSyncTaskBucket"
    effect  = "Allow"
    actions = ["s3:ListBucket"]
    resources = [
      data.aws_s3_bucket.sync.arn,
    ]
  }

  statement {
    sid    = "AllowSyncTaskObjects"
    effect = "Allow"
    actions = [
      "s3:PutObject",
      "s3:GetObject",
      "s3:DeleteObject",
      "s3:ListObject",
      "s3:ListBucket",
    ]
    resources = [
      "${data.aws_s3_bucket.sync.arn}/*",
    ]
  }

  statement {
    sid    = "AllowSyncTaskKMS"
    effect = "Allow"
    actions = [
      "kms:Decrypt",
      "kms:DescribeKey",
      "kms:Encrypt",
      "kms:GenerateDataKeyPair",
      "kms:GenerateDataKeyPairWithoutPlaintext",
      "kms:GenerateDataKeyWithoutPlaintext",
      "kms:ReEncrypt"
    ]
    resources = [
      data.aws_kms_alias.backup.target_key_arn,
    ]
  }
}

data "aws_s3_bucket" "sync" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = aws.management
}

data "aws_kms_alias" "backup" {
  name     = "alias/backup"
  provider = aws.management
}
