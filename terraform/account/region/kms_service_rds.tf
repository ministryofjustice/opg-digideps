##### Shared KMS key for RDS #####

# Account RDS encryption
module "rds_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "RDS"
  kms_key_alias_name      = "digideps_rds_encryption_key"
  enable_key_rotation     = true
  enable_multi_region     = false
  deletion_window_in_days = 10
  kms_key_policy          = var.account.name == "development" ? data.aws_iam_policy_document.kms_rds_merged_for_development.json : data.aws_iam_policy_document.kms_rds_merged.json
  providers = {
    aws.eu_west_1 = aws.eu_west_1
    aws.eu_west_2 = aws.eu_west_2
  }
}

# Policies
data "aws_iam_policy_document" "kms_rds_merged_for_development" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_rds.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_rds_merged" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_rds.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}


data "aws_iam_policy_document" "kms_rds" {

  # ----------------------------
  # Allow Key to be used for Encryption
  # ----------------------------
  statement {
    sid    = "AllowKeyToBeUsedForEncryption"
    effect = "Allow"

    actions = [
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:Encrypt",
      "kms:DescribeKey",
      "kms:CreateGrant"
    ]

    resources = [
      "arn:aws:kms:*:${data.aws_caller_identity.current.account_id}:key/*"
    ]

    principals {
      type        = "AWS"
      identifiers = ["*"]
    }

    # kms:CallerAccount
    condition {
      test     = "StringEquals"
      variable = "kms:CallerAccount"
      values = [
        data.aws_caller_identity.current.account_id,
        local.backup_account_id
      ]
    }

    # kms:ViaService
    condition {
      test     = "StringLike"
      variable = "kms:ViaService"
      values = [
        "rds.*.amazonaws.com",
        "backup.*.amazonaws.com"
      ]
    }

    # aws:PrincipalArn
    condition {
      test     = "StringLike"
      variable = "aws:PrincipalArn"
      values = [
        aws_iam_role.vault_backup_role.arn,
        "arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/breakglass",
        "arn:aws:iam::${local.backup_account_id}:role/aws-service-role/backup.amazonaws.com/AWSServiceRoleForBackup"
      ]
    }
  }


  # ----------------------------
  # Allow Key to be used for Decryption
  # ----------------------------
  statement {
    sid    = "AllowKeyToBeUsedForDecryption"
    effect = "Allow"

    actions = [
      "kms:DescribeKey",
      "kms:Decrypt"
    ]

    resources = [
      "arn:aws:kms:*:${data.aws_caller_identity.current.account_id}:key/*"
    ]

    principals {
      type        = "AWS"
      identifiers = ["*"]
    }

    # kms:CallerAccount
    condition {
      test     = "StringEquals"
      variable = "kms:CallerAccount"
      values = [
        data.aws_caller_identity.current.account_id,
        local.backup_account_id
      ]
    }

    # kms:ViaService
    condition {
      test     = "StringLike"
      variable = "kms:ViaService"
      values = [
        "rds.*.amazonaws.com",
        "backup.*.amazonaws.com"
      ]
    }

    # aws:PrincipalArn
    condition {
      test     = "StringLike"
      variable = "aws:PrincipalArn"
      values = [
        aws_iam_role.vault_backup_role.arn,
        "arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/breakglass",
        "arn:aws:iam::${local.backup_account_id}:role/aws-service-role/backup.amazonaws.com/AWSServiceRoleForBackup"
      ]
    }
  }
}
