# ----------------------------
# Backup Vault
# ----------------------------
resource "aws_backup_vault" "immutable_vault" {
  name        = "backup-vault-${var.account.name}"
  kms_key_arn = module.backup_kms.eu_west_1_target_key_arn
}

resource "aws_backup_vault_lock_configuration" "vault_lock" {
  backup_vault_name   = aws_backup_vault.immutable_vault.name
  min_retention_days  = 1
  max_retention_days  = 365
  changeable_for_days = 7
}

#resource "aws_backup_vault_policy" "immutable_vault" {
#  backup_vault_name = local.backup_account_id
#  policy            = data.aws_iam_policy_document.cross_account_copy_policy.json
#}
#
#data "aws_iam_policy_document" "cross_account_copy_policy" {
#  statement {
#    sid    = "AllowSourceAccountBackupCopy"
#    effect = "Allow"
#    principals {
#      type = "AWS"
#      identifiers = [
#        "arn:aws:iam::${local.backup_account_id}:root"
#      ]
#    }
#    actions = [
#      "backup:CopyIntoBackupVault"
#    ]
#    resources = [
#      "*"
#    ]
#  }
#}

# ----------------------------
# Backup Service Role
# ----------------------------
resource "aws_iam_role" "vault_backup_role" {
  name               = "digideps-vault-backup-role"
  assume_role_policy = data.aws_iam_policy_document.assume_vault_backup_role.json
}

data "aws_iam_policy_document" "assume_vault_backup_role" {
  statement {
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["backup.amazonaws.com"]
    }
  }
}

resource "aws_iam_role_policy_attachment" "vault_backup_role" {
  role       = aws_iam_role.vault_backup_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSBackupServiceRolePolicyForBackup"
}

data "aws_kms_alias" "backup_rds" {
  name = "alias/aws/rds"
}

data "aws_iam_policy_document" "vault_backup_kms" {
  statement {
    sid    = "AllowKMSOperations"
    effect = "Allow"
    resources = [
      "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/mrk-*",
      module.backup_kms.eu_west_1_target_key_arn,
      data.aws_kms_alias.backup_rds.target_key_arn
    ]
    actions = [
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:Encrypt",
      "kms:DescribeKey",
      "kms:Decrypt",
      "kms:CreateGrant"
    ]
  }
}

resource "aws_iam_policy" "vault_backup_kms" {
  name   = "vault-backup-kms-operations-${var.account.name}"
  policy = data.aws_iam_policy_document.vault_backup_kms.json
}

resource "aws_iam_role_policy_attachment" "vault_backup_kms" {
  role       = aws_iam_role.vault_backup_role.name
  policy_arn = aws_iam_policy.vault_backup_kms.arn
}

# ----------------------------
# Backup Plan (rule to snapshot RDS daily)
# ----------------------------
resource "aws_backup_plan" "rds_backup_plan" {
  name = "backup-plan-${var.account.name}"

  # Rule 1 – daily snapshot with cross-account copy
  rule {
    rule_name                = "continuous-rds-backup"
    target_vault_name        = aws_backup_vault.immutable_vault.name
    enable_continuous_backup = true

    lifecycle {
      delete_after = 14
    }
  }

  # Rule 2 – daily snapshot with cross-account copy
  rule {
    rule_name         = "daily-snapshot-copy"
    target_vault_name = aws_backup_vault.immutable_vault.name
    schedule          = "cron(0 07 * * ? *)"

    lifecycle {
      delete_after = 14
    }

    copy_action {
      destination_vault_arn = local.backup_vault

      lifecycle {
        delete_after = 14
      }
    }
  }
}

# ----------------------------
# Backup Selection - tag based
# ----------------------------
resource "aws_backup_selection" "rds_selection" {
  name         = "rds-tag-selection"
  iam_role_arn = aws_iam_role.vault_backup_role.arn
  plan_id      = aws_backup_plan.rds_backup_plan.id

  selection_tag {
    type  = "STRINGEQUALS"
    key   = "backup_to_vault"
    value = "true"
  }
}

locals {
  backup_account_id = "238302996107"
  backup_vault      = "arn:aws:backup:${data.aws_region.current.name}:${local.backup_account_id}:backup-vault:digideps-${data.aws_region.current.name}-${var.account.name}-backup"
}
