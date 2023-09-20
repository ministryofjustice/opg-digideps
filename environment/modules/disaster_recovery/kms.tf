resource "aws_kms_alias" "backup_kms_alias" {
  name          = "alias/digideps-ca-db-backup"
  target_key_id = aws_kms_key.db_backup.id

  depends_on = [aws_kms_key.db_backup]
}

resource "aws_kms_key" "db_backup" {
  description             = "KMS DB secondary backup"
  deletion_window_in_days = 10
  policy                  = data.aws_iam_policy_document.kms_db_backup_key.json
  enable_key_rotation     = true
  tags                    = var.default_tags
}

data "aws_iam_policy_document" "kms_db_backup_key" {
  statement {
    sid    = "Enable KMS administration"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${var.backup_account_id}:role/${var.cross_account_role_name}",
        "arn:aws:iam::${var.backup_account_id}:role/breakglass"
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
        "arn:aws:iam::${var.account_id}:root"
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
