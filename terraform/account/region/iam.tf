locals {
  sirius_root       = "arn:aws:iam::${var.account.sirius_account_id}:root"
  sirius_env_lambda = "arn:aws:iam::${var.account.sirius_account_id}:role/deputy-reporting-${var.account.name}-v2"
}

# ===== Integrations Lambda S3 Access Role =====
# INFO - This role is assumed by the opg-data-deputy-reporting integration lambda to move documents to sirius
resource "aws_iam_role" "integrations_s3_read" {
  assume_role_policy = data.aws_iam_policy_document.integrations_assume_role.json
  name               = "integrations-s3-read-${var.account.name}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "integrations_assume_role" {
  statement {
    sid    = "AllowIntegrationsLambdaS3"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = var.account.name == "development" ? [local.sirius_root] : [local.sirius_env_lambda]
    }
    actions = ["sts:AssumeRole"]
  }
}

data "aws_iam_policy_document" "integrations_s3_read" {
  statement {
    sid       = "AllowIntegrationsLambdaS3"
    effect    = "Allow"
    resources = var.account.name == "development" ? ["arn:aws:s3:::pa-uploads-*"] : ["arn:aws:s3:::pa-uploads-${local.s3_bucket}", "arn:aws:s3:::pa-uploads-${local.s3_bucket}/*"]
    actions   = ["s3:GetObject", "s3:ListBucket"]
  }
}

resource "aws_iam_policy" "integrations_s3_read" {
  name   = "integration-s3-read-${var.account.name}"
  policy = data.aws_iam_policy_document.integrations_s3_read.json
}

resource "aws_iam_role_policy_attachment" "access_policy_attachment" {
  role       = aws_iam_role.integrations_s3_read.id
  policy_arn = aws_iam_policy.integrations_s3_read.arn
}

# ===== RDS Enhanced Monitoring Role =====
# INFO - This role is required for enhanced rds monitoring.

resource "aws_iam_role" "enhanced_monitoring" {
  name               = "rds-enhanced-monitoring"
  assume_role_policy = data.aws_iam_policy_document.enhanced_monitoring.json
  tags = merge(
    var.default_tags,
    { Name = "rds-enhanced-monitoring-role-${var.account.name}" },
  )
}

resource "aws_iam_role_policy_attachment" "enhanced_monitoring" {
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
  role       = aws_iam_role.enhanced_monitoring.name
}

data "aws_iam_policy_document" "enhanced_monitoring" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["monitoring.rds.amazonaws.com"]
      type        = "Service"
    }
  }
}

# ===== Task Runner Role =====
# INFO - This role is required for running single tasks (restore, backup, run-task, integration tests etc...) TODO - split this out.

resource "aws_iam_role" "sync" {
  assume_role_policy = data.aws_iam_policy_document.sync_assume_policy.json
  name               = "sync"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "sync_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "sync" {
  name   = "sync"
  policy = data.aws_iam_policy_document.sync.json
  role   = aws_iam_role.sync.id
}

data "aws_iam_policy_document" "sync" {
  statement {
    sid     = "AllowSyncTaskBucket"
    effect  = "Allow"
    actions = ["s3:ListBucket"]
    resources = [
      data.aws_s3_bucket.sync.arn,
    ]
  }

  statement {
    sid    = "AllowSyncTaskObjects"
    effect = "Allow"
    actions = [
      "s3:*Object*"
    ]
    #tfsec:ignore:aws-iam-no-policy-wildcards - Not overly permissive, permissions only on sync bucket
    resources = [
      "${data.aws_s3_bucket.sync.arn}/*",
    ]
  }

  statement {
    sid    = "AllowSyncTaskKMS"
    effect = "Allow"
    actions = [
      "kms:*"
    ]
    resources = [
      data.aws_kms_alias.backup.target_key_arn,
    ]
  }

  statement {
    sid    = "AllowQuerySecretsManager"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue"
    ]
    resources = [
      "arn:aws:secretsmanager:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:secret:*/public-jwt-key-base64*",
      "arn:aws:secretsmanager:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:secret:*/private-jwt-key-base64*"
    ]
  }
}

data "aws_s3_bucket" "sync" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = aws.management
}

data "aws_kms_alias" "backup" {
  name     = "alias/backup"
  provider = aws.management
}
