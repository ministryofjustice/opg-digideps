resource "aws_iam_role" "cross_acc_backup" {
  assume_role_policy = data.aws_iam_policy_document.cross_acc_backup_assume.json
  name               = "cross-acc-db-backup.${var.environment}"
  tags               = var.default_tags
  provider           = aws.backup
}

data "aws_iam_policy_document" "cross_acc_backup_assume" {
  statement {
    sid    = "AllowBackupAccount"
    effect = "Allow"
    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${var.account.account_id}:root",
        aws_iam_role.dr_backup.arn
      ]
    }
    actions = ["sts:AssumeRole"]
  }
  provider = aws.backup
}

resource "aws_iam_role_policy" "cross_acc_backup" {
  name     = "cross-acc-backup.${var.environment}"
  policy   = data.aws_iam_policy_document.cross_acc_backup_policy.json
  role     = aws_iam_role.cross_acc_backup.id
  provider = aws.backup
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
  provider = aws.backup
}
