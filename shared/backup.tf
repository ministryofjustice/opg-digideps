resource "aws_kms_key" "backup" {
  description = "Encryption of S3 objects allowing cross account access"
  policy      = data.aws_iam_policy_document.backup.json
}

resource "aws_kms_alias" "backup" {
  name          = "alias/backup"
  target_key_id = aws_kms_key.backup.key_id
}

data "aws_iam_policy_document" "backup" {
  statement {
    sid    = "Enable IAM User Permissions"
    effect = "Allow"

    principals {
      identifiers = [
        "arn:aws:iam::${local.account.account_id}:root",
      ]
      type = "AWS"
    }

    actions = [
      "kms:*",
    ]

    resources = ["*"]
  }

  statement {
    sid    = "Allow use of the key"
    effect = "Allow"

    principals {
      identifiers = [
        "arn:aws:iam::${var.accounts.production.account_id}:root",
        "arn:aws:iam::${var.accounts.preproduction.account_id}:root",
        "arn:aws:iam::${var.accounts.development.account_id}:root",
      ]
      type = "AWS"
    }

    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey",
    ]

    resources = ["*"]
  }

  statement {
    sid    = "Allow attachment of persistent resources"
    effect = "Allow"

    principals {
      identifiers = [
        "arn:aws:iam::${var.accounts.production.account_id}:root",
        "arn:aws:iam::${var.accounts.preproduction.account_id}:root",
        "arn:aws:iam::${var.accounts.development.account_id}:root",
      ]
      type = "AWS"
    }

    actions = [
      "kms:CreateGrant",
      "kms:ListGrants",
      "kms:RevokeGrant"
    ]

    resources = ["*"]

    condition {
      test     = "Bool"
      values   = ["true"]
      variable = "kms:GrantIsForAWSResource"
    }
  }
}
