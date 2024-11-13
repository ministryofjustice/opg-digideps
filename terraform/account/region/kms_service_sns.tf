##### Shared KMS key for SNS #####

# Account logs encryption
module "sns_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "SNS"
  kms_key_alias_name      = "digideps_sns_encryption_key"
  enable_key_rotation     = true
  enable_multi_region     = false
  deletion_window_in_days = 10
  kms_key_policy          = var.account.name == "development" ? data.aws_iam_policy_document.kms_sns_merged_for_development.json : data.aws_iam_policy_document.kms_sns_merged.json
  providers = {
    aws.eu_west_1 = aws.eu_west_1
    aws.eu_west_2 = aws.eu_west_2
  }
}

# Policies
data "aws_iam_policy_document" "kms_sns_merged_for_development" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_sns.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_sns_merged" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_sns.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "kms_sns" {
  statement {
    sid       = "Allow Key to be used for Encryption by SNS"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey"
    ]

    principals {
      type = "Service"
      identifiers = [
        "events.amazonaws.com",
        "cloudwatch.amazonaws.com"
      ]
    }
  }

  statement {
    sid       = "Allow Key to be decrypted by lambda"
    effect    = "Allow"
    resources = ["*"]
    actions   = ["kms:Decrypt"]

    principals {
      type = "AWS"
      identifiers = [
        aws_iam_role.lambda_monitor_notify.arn
      ]
    }
  }
}
