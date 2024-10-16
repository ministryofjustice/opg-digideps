# TODO next PR to enable this.. doing in stages to avoid breaking things
#module "sns_kms" {
#  source                  = "./modules/kms_key"
#  encrypted_resource      = "SNS"
#  kms_key_alias_name      = "digideps_sns_encryption_key"
#  enable_key_rotation     = true
#  enable_multi_region     = true
#  deletion_window_in_days = 10
#  kms_key_policy          = var.account.name == "development" ? data.aws_iam_policy_document.kms_sns_merged_for_development.json : data.aws_iam_policy_document.kms_sns_merged.json
#  providers = {
#    aws.eu_west_1 = aws.eu_west_1
#    aws.eu_west_2 = aws.eu_west_2
#  }
#}
#
#data "aws_iam_policy_document" "kms_sns_merged_for_development" {
#  provider = aws.global
#  source_policy_documents = [
#    data.aws_iam_policy_document.kms_sns.json,
#    data.aws_iam_policy_document.kms_base_permissions.json,
#    data.aws_iam_policy_document.kms_development_account_operator_admin.json
#  ]
#}
#
#data "aws_iam_policy_document" "kms_sns_merged" {
#  provider = aws.global
#  source_policy_documents = [
#    data.aws_iam_policy_document.kms_sns.json,
#    data.aws_iam_policy_document.kms_base_permissions.json
#  ]
#}
#
## SNS Policy
#data "aws_iam_policy_document" "kms_sns" {
#  provider = aws.global
#  statement {
#    sid    = "Allow Key to be used for Decryption"
#    effect = "Allow"
#    resources = [
#      "arn:aws:kms:*:${data.aws_caller_identity.current.account_id}:key/*"
#    ]
#    actions = [
#      "kms:GenerateDataKey*",
#      "kms:Decrypt",
#      "kms:DescribeKey",
#    ]
#
#    principals {
#      type = "AWS"
#      identifiers = [
#        aws_iam_role.lambda_monitor_notify.arn,
#      ]
#    }
#  }
#
#  statement {
#    sid    = "Allow Key to be used for Decryption"
#    effect = "Allow"
#    resources = [
#      "arn:aws:kms:*:${data.aws_caller_identity.current.account_id}:key/*"
#    ]
#    actions = [
#      "kms:Decrypt",
#      "kms:GenerateDataKey*",
#      "kms:DescribeKey",
#    ]
#
#    principals {
#      type = "Service"
#      identifiers = [
#        "sns.amazonaws.com"
#      ]
#    }
#  }
#}
