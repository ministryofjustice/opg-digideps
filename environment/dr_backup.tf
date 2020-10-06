// Backup account

resource "aws_iam_role" "cross_acc_backup" {
  assume_role_policy = data.aws_iam_policy_document.cross_acc_backup_assume.json
  name               = "cross-acc-db-backup.${local.environment}"
  tags               = local.default_tags
  provider           = aws.sandbox
}

data "aws_iam_policy_document" "cross_acc_backup_assume" {
  statement {
    sid    = "AllowBackupAccount"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::248804316466:root",
        aws_iam_role.dr_backup.arn
      ]
    }
    actions = ["sts:AssumeRole"]
  }
  provider = aws.sandbox
}

resource "aws_iam_role_policy" "cross_acc_backup" {
  name     = "cross-acc-backup.${local.environment}"
  policy   = data.aws_iam_policy_document.cross_acc_backup_policy.json
  role     = aws_iam_role.cross_acc_backup.id
  provider = aws.sandbox
}

data "aws_iam_policy_document" "cross_acc_backup_policy" {
  statement {
    sid    = "AllAdminActionsCalledOnS3Bucket"
    effect = "Allow"
    actions = [
      "rds:CopyDbSnapshot",
      "rds:CopyDBClusterSnapshot",
      "rds:DescribeDbSnapshots",
      "rds:DescribeDbClusterSnapshots",
      "rds:DeleteDBClusterSnapshot",
      "rds:DeleteDBSnapshot"
    ]
    resources = [
      "*"
    ]
  }
  statement {
    sid    = "AllKMSAccess"
    effect = "Allow"
    actions = [
      "kms:CreateGrant",
      "kms:DescribeKey"
    ]
    resources = [
      "*"
    ]
  }
  provider = aws.sandbox
}

// KMS key

resource "aws_kms_key" "db_backup" {
  description             = "KMS DB secondary backup"
  deletion_window_in_days = 10
  policy                  = data.aws_iam_policy_document.kms_db_backup_key.json
  tags                    = local.default_tags
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
