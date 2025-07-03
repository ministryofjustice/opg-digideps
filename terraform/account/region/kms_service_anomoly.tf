##### Shared KMS key for Anomaly #####

# Account logs encryption
module "anomaly_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "LogAnomolyDetection"
  kms_key_alias_name      = "digideps_anomaly_encryption_key"
  enable_key_rotation     = true
  enable_multi_region     = false
  deletion_window_in_days = 10
  kms_key_policy          = var.account.name == "development" ? data.aws_iam_policy_document.kms_anomaly_merged_for_development.json : data.aws_iam_policy_document.kms_anomaly_merged.json
  providers = {
    aws.eu_west_1 = aws.eu_west_1
    aws.eu_west_2 = aws.eu_west_2
  }
}

# Policies
data "aws_iam_policy_document" "kms_anomaly_merged_for_development" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.cloudwatch_anomaly_kms_key_policy.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_anomaly_merged" {
  provider = aws.global
  source_policy_documents = [
    data.aws_iam_policy_document.cloudwatch_anomaly_kms_key_policy.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "cloudwatch_anomaly_kms_key_policy" {
  statement {
    sid    = "AllowCloudWatchToUseKeyForAnomalyDetection"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["logs.${data.aws_region.current.name}.amazonaws.com"]
    }

    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:GenerateDataKey*",
      "kms:DescribeKey"
    ]

    resources = ["*"]

    condition {
      test     = "ArnLike"
      variable = "kms:EncryptionContext:aws:logs:arn"

      values = [
        "arn:aws:logs:${data.aws_region.current.name}:${var.account.account_id}:anomaly-detector:*"
      ]
    }
  }

  statement {
    sid    = "AllowCloudWatchToUseKeyForAnomalyDetectionReencrypt"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["logs.${data.aws_region.current.name}.amazonaws.com"]
    }

    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey"
    ]

    resources = ["*"]

    condition {
      test     = "ArnLike"
      variable = "kms:EncryptionContext:aws-crypto-ec:aws:logs:arn"

      values = [
        "arn:aws:logs:${data.aws_region.current.name}:${var.account.account_id}:anomaly-detector:*"
      ]
    }
  }
}
