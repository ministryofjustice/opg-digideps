// DATA SOURCE FOR DEFAULT KEY
data "aws_kms_alias" "source_default_key" {
  name = "alias/aws/s3"
}

// CREATE BACKUP ROLE USED FOR LOCAL AND CROSS ACCOUNT REPLICATION
resource "aws_iam_role" "backup_role" {
  name               = "digideps-backup-role.${local.environment}"
  description        = "IAM Role for s3 replication in ${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.backup_role_policy.json
}

// CREATE INSTANCE PROFILE
resource "aws_iam_instance_profile" "backup" {
  name = "cross-account-backup-role-${terraform.workspace}"
  role = aws_iam_role.backup_role.name
}

// ALLOW ASSUME ROLE ON BACKUP ROLE
data "aws_iam_policy_document" "backup_role_policy" {
  statement {
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["s3.amazonaws.com"]
    }
    actions = ["sts:AssumeRole"]
  }
}

// ATTACH THE ASSUME ROLE POLICY
resource "aws_iam_role_policy_attachment" "backup_policy_attachment" {
  role       = aws_iam_role.backup_role.name
  policy_arn = aws_iam_policy.backup_policy.arn
}

// POLICY ATTACHMENT
resource "aws_iam_policy" "backup_policy" {
  name_prefix = "digideps-backup-policy"
  description = "IAM Policy for s3 replication in ${terraform.workspace}"
  policy      = data.aws_iam_policy_document.replication_policy.json
}

resource "aws_iam_role_policy_attachment" "replication" {
  role       = aws_iam_role.backup_role.name
  policy_arn = aws_iam_policy.backup_policy.arn
}

//REPLICATION POLICY FOR BACKUP ROLE
data "aws_iam_policy_document" "replication_policy" {

  statement {
    sid    = "AllowReplicationConfiguration"
    effect = "Allow"
    actions = [
      "s3:ListBucket",
      "s3:GetReplicationConfiguration",
      "s3:GetObjectVersionForReplication",
      "s3:GetObjectVersionAcl",
      "s3:GetObjectVersionTagging",
      "s3:GetObjectRetention",
      "s3:GetObjectLegalHold"
    ]
    #trivy:ignore:avd-aws-0057 - Not overly permissive
    resources = [
      "${module.pa_uploads.arn}/*",
      module.pa_uploads.arn
    ]
  }

  statement {
    sid    = "AllowReplication"
    effect = "Allow"
    actions = [
      "s3:ReplicateObject",
      "s3:ReplicateDelete",
      "s3:ReplicateTags",
      "s3:GetObjectVersionTagging",
      "s3:ObjectOwnerOverrideToBucketOwner"
    ]

    condition {
      test     = "StringLikeIfExists"
      variable = "s3:x-amz-server-side-encryption"
      values = [
        "aws:kms",
        "AES256"
      ]
    }

    condition {
      test     = "StringLikeIfExists"
      variable = "s3:x-amz-server-side-encryption-aws-kms-key-id"
      values = [
        "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${var.account.s3_backup_kms_arn}"
      ]
    }
    #trivy:ignore:avd-aws-0057 - Not overly permissive
    resources = [
      "arn:aws:s3:::${var.account.name}.backup.digideps.opg.service.justice.gov.uk/*",
      "${local.replication_bucket}/*"
    ]
  }

  statement {
    sid    = "Decrypt"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]

    condition {
      test     = "StringLike"
      variable = "kms:ViaService"

      values = [
        "s3.${data.aws_region.current.name}.amazonaws.com",
      ]
    }

    condition {
      test     = "StringLike"
      variable = "kms:EncryptionContext:aws:s3:arn"

      values = [
        "${module.pa_uploads.arn}/*",
      ]
    }

    resources = [
      data.aws_kms_alias.source_default_key.target_key_arn,
      aws_kms_key.s3.arn
    ]
  }

  statement {
    sid    = "Encrypt"
    effect = "Allow"
    actions = [
      "kms:Encrypt"
    ]

    condition {
      test     = "StringLike"
      variable = "kms:ViaService"

      values = [
        "s3.eu-west-1.amazonaws.com",
      ]
    }

    condition {
      test     = "StringLike"
      variable = "kms:EncryptionContext:aws:s3:arn"

      values = [
        "arn:aws:s3:::${var.account.name}.backup.digideps.opg.service.justice.gov.uk/*",
      ]
    }

    resources = ["arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${var.account.s3_backup_kms_arn}"]
  }
}
