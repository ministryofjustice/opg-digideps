##### Application Log Group #####
resource "aws_cloudwatch_log_group" "opg_digi_deps" {
  name              = local.environment
  retention_in_days = 180
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags              = var.default_tags
}

resource "aws_cloudwatch_log_data_protection_policy" "opensearch_pipeline" {
  log_group_name = aws_cloudwatch_log_group.opg_digi_deps.name
  policy_document = jsonencode(merge(
    jsondecode(file("${path.root}/cloudwatch_log_data_protection_policy/cloudwatch_log_data_protection_policy.json")),
    {
      Name = "data-protection-${local.environment}-opensearch-ingestion"
    }
  ))
  provider = aws.eu_west_1
}

##### Audit Log Group #####
resource "aws_cloudwatch_log_group" "audit" {
  name       = "audit-${local.environment}"
  kms_key_id = aws_kms_key.cloudwatch_logs.arn
  tags       = var.default_tags
}

##### Shared KMS key for logs #####
resource "aws_kms_key" "cloudwatch_logs" {
  description             = "Digideps cloudwatch logs for ${local.environment}"
  deletion_window_in_days = 10
  enable_key_rotation     = true
  policy                  = data.aws_iam_policy_document.cloudwatch_kms.json
}

resource "aws_kms_alias" "cloudwatch_logs_alias" {
  name          = "alias/digideps-application-cloudwatch-${local.environment}"
  target_key_id = aws_kms_key.cloudwatch_logs.key_id
}

##### Shared Policy #####

# See the following link for further information
# https://docs.aws.amazon.com/kms/latest/developerguide/key-policies.html
data "aws_iam_policy_document" "cloudwatch_kms" {
  statement {
    sid       = "Enable Root account permissions on Key"
    effect    = "Allow"
    actions   = ["kms:*"]
    resources = ["*"]

    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${data.aws_caller_identity.current.account_id}:root",
      ]
    }
  }

  statement {
    sid       = "Allow Key to be used for Encryption"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey",
    ]

    principals {
      type = "Service"
      identifiers = [
        "logs.${data.aws_region.current.name}.amazonaws.com",
        "events.amazonaws.com"
      ]
    }
  }

  statement {
    sid       = "Key Administrator"
    effect    = "Allow"
    resources = ["*"]
    actions = [
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
      "kms:TagResource",
      "kms:UntagResource",
      "kms:ScheduleKeyDeletion",
      "kms:CancelKeyDeletion"
    ]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/breakglass"]
    }
  }
}
