##### Shared KMS key for S3 #####

# Account logs encryption
module "s3_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "S3"
  kms_key_alias_name      = "digideps_s3_encryption_key"
  enable_key_rotation     = true
  enable_multi_region     = false
  deletion_window_in_days = 10
  kms_key_policy          = var.account.name == "development" ? data.aws_iam_policy_document.kms_s3_merged_for_development.json : data.aws_iam_policy_document.kms_s3_merged.json
  providers = {
    aws.eu_west_1 = aws.eu_west_1
    aws.eu_west_2 = aws.eu_west_2
  }
}

# Policies
data "aws_iam_policy_document" "kms_s3_merged_for_development" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_s3.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_s3_merged" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_s3.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "kms_s3" {
  statement {
    sid       = "Allow Key to be used for Encryption by S3"
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
        "s3.amazonaws.com"
      ]
    }
  }
}
