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

# ----------------------------
# Backup Service Role
# ----------------------------
resource "aws_iam_role" "aws_backup_service_role" {
  name               = "AWSBackupServiceRoleForRDS"
  assume_role_policy = data.aws_iam_policy_document.backup_assume_role_policy.json
}

# Assume role policy
data "aws_iam_policy_document" "backup_assume_role_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["backup.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy_attachment" "backup_service_attach" {
  role       = aws_iam_role.aws_backup_service_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSBackupServiceRolePolicyForBackup"
}

# ----------------------------
# Backup Plan (rule to snapshot RDS daily)
# ----------------------------
resource "aws_backup_plan" "rds_backup_plan" {
  name = "backup-plan-${var.account.name}"

  rule {
    rule_name                = "daily-rds-snapshots"
    target_vault_name        = aws_backup_vault.immutable_vault.name
    schedule                 = "cron(0 05 * * ? *)"
    enable_continuous_backup = true

    lifecycle {
      delete_after = 1
    }
  }
}

# ----------------------------
# Backup Selection - tag based
# ----------------------------
resource "aws_backup_selection" "rds_selection" {
  name         = "rds-tag-selection"
  iam_role_arn = aws_iam_role.aws_backup_service_role.arn
  plan_id      = aws_backup_plan.rds_backup_plan.id

  selection_tag {
    type  = "STRINGEQUALS"
    key   = "backup_to_vault"
    value = "true"
  }
}
