##### Shared KMS key for DynamoDB #####

# Account dynamodb encryption
module "dynamodb_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "DynamoDB"
  kms_key_alias_name      = "digideps_dynamodb_encryption_key"
  enable_key_rotation     = true
  enable_multi_region     = false
  deletion_window_in_days = 10
  kms_key_policy          = var.account.name == "development" ? data.aws_iam_policy_document.kms_dynamodb_merged_for_development.json : data.aws_iam_policy_document.kms_dynamodb_merged.json
  providers = {
    aws.eu_west_1 = aws.eu_west_1
    aws.eu_west_2 = aws.eu_west_2
  }
}

# Policies
data "aws_iam_policy_document" "kms_dynamodb_merged_for_development" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_dynamodb.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_dynamodb_merged" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.kms_dynamodb.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "kms_dynamodb" {
  statement {
    sid       = "Allow Key to be used for Encryption by DynamoDB"
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
        "dynamodb.amazonaws.com"
      ]
    }
  }
}
