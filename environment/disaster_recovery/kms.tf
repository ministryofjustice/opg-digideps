resource "aws_kms_key" "db_backup" {
  description             = "KMS DB secondary backup"
  deletion_window_in_days = 10
  policy                  = data.aws_iam_policy_document.kms_db_backup_key.json
  tags                    = var.default_tags
}

data "aws_iam_policy_document" "kms_db_backup_key" {
  statement {
    sid    = "Enable KMS administration"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        aws_iam_role.cross_acc_backup.arn
      ]
    }
    resources = ["*"]
    actions = [
      "kms:CreateGrant",
      "kms:DescribeKey"
    ]
  }

  statement {
    sid    = "KMS admin"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::248804316466:root"
      ]
    }
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt",
      "kms:GenerateDataKey*",
      "kms:Create*",
      "kms:Describe*",
      "kms:Enable*",
      "kms:List*",
      "kms:Put*",
      "kms:Update*",
      "kms:Revoke*",
      "kms:Disable*",
      "kms:Get*",
      "kms:Delete*",
      "kms:ScheduleKeyDeletion",
      "kms:CancelKeyDeletion"
    ]
  }
}
