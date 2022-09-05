locals {
  non-replication_workspaces = ["production02", "preproduction", "training", "integration", "development"]
  bucket_replication_status  = contains(local.non-replication_workspaces, local.environment) ? "Disabled" : "Enabled"
  long_expiry_workspaces     = ["production02", "development", "training"]
  expiration_days            = contains(local.long_expiry_workspaces, local.environment) ? 490 : 14
  noncurrent_expiration_days = contains(local.long_expiry_workspaces, local.environment) ? 365 : 7
}

data "aws_region" "current" {}

// GET DEV REPLICATION BUCKET
data "aws_s3_bucket" "replication_bucket" {
  bucket   = "pa-uploads-branch-replication"
  provider = aws.development
}

// DATA SOURCE FOR DEFAULT KEY
data "aws_kms_alias" "source_default_key" {
  name = "alias/aws/s3"
}

#module "pa_uploads" {
#  source = "./modules/s3_bucket"
#
#  account_name  = local.account.account_name
#  bucket_name   = "pa-uploads-${local.environment}"
#  force_destroy = local.account.force_destroy_bucket
#  providers = {
#    aws = aws
#  }
#}

// CREATE THE MAIN BUCKET
resource "aws_s3_bucket" "pa_uploads" {
  bucket        = "pa-uploads-${local.environment}"
  acl           = "private"
  force_destroy = local.account["force_destroy_bucket"]

  versioning {
    enabled = true
  }

  logging {
    target_bucket = aws_s3_bucket.s3_access_logs.id
    target_prefix = "pa_uploads/"
  }

  lifecycle_rule {
    enabled = true

    expiration {
      days = local.expiration_days
    }

    noncurrent_version_expiration {
      days = local.noncurrent_expiration_days
    }
  }

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "AES256"
      }
    }
  }

  replication_configuration {
    role = aws_iam_role.backup_role.arn

    rules {
      id       = "ReplicationCrossAccount"
      priority = 1
      status   = local.account.s3_backup_replication

      filter {}

      destination {
        account_id         = local.backup_account_id
        bucket             = "arn:aws:s3:::${local.account.name}.backup.digideps.opg.service.justice.gov.uk"
        replica_kms_key_id = "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${local.account.s3_backup_kms_arn}"

        access_control_translation {
          owner = "Destination"
        }
      }

      source_selection_criteria {
        sse_kms_encrypted_objects {
          enabled = true
        }
      }
    }
    rules {
      id       = "ReplicationLocalDevelopment"
      priority = 2
      status   = local.bucket_replication_status

      filter {}

      destination {
        bucket        = data.aws_s3_bucket.replication_bucket.arn
        storage_class = "STANDARD"
      }
    }
  }
  tags = local.default_tags
}

// BLOCK PUBLIC ACCESS ON PA_UPLOADS
resource "aws_s3_bucket_public_access_block" "pa_uploads" {
  bucket = aws_s3_bucket.pa_uploads.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

// BASE POLICY FOR PA_UPLOADS
data "aws_iam_policy_document" "pa_uploads" {
  policy_id = "PutObjPolicy"

  statement {
    sid    = "DenyUnEncryptedObjectUploads"
    effect = "Deny"

    principals {
      identifiers = ["*"]
      type        = "AWS"
    }

    actions   = ["s3:PutObject"]
    resources = ["${aws_s3_bucket.pa_uploads.arn}/*"]

    condition {
      test     = "StringNotEquals"
      values   = ["AES256"]
      variable = "s3:x-amz-server-side-encryption"
    }
  }

  statement {
    sid    = "DenyNoneSSLRequests"
    effect = "Deny"
    actions = [
    "s3:*"]
    resources = [
      aws_s3_bucket.pa_uploads.arn,
      "${aws_s3_bucket.pa_uploads.arn}/*"
    ]

    condition {
      test     = "Bool"
      variable = "aws:SecureTransport"
      values = [
      false]
    }

    principals {
      type        = "AWS"
      identifiers = ["*"]
    }
  }

  statement {
    sid     = "DelegateS3Access"
    effect  = "Allow"
    actions = ["s3:ListBucket", "s3:GetObject"]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${local.backup_account_id}:root"]
    }

    resources = [
      aws_s3_bucket.pa_uploads.arn,
      "${aws_s3_bucket.pa_uploads.arn}/*"
    ]
  }
}

// ATTACH BASE POLICY FOR PA_UPLOADS
resource "aws_s3_bucket_policy" "pa_uploads" {
  bucket = aws_s3_bucket_public_access_block.pa_uploads.bucket
  policy = data.aws_iam_policy_document.pa_uploads.json
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
    resources = [
      "${aws_s3_bucket.pa_uploads.arn}/*",
      aws_s3_bucket.pa_uploads.arn
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
        "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${local.account.s3_backup_kms_arn}"
      ]
    }
    #tfsec:ignore:aws-iam-no-policy-wildcards - Not overly permissive
    resources = [
      "arn:aws:s3:::${local.account.name}.backup.digideps.opg.service.justice.gov.uk/*",
      "${data.aws_s3_bucket.replication_bucket.arn}/*"
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
        "${aws_s3_bucket.pa_uploads.arn}/*",
      ]
    }

    resources = [data.aws_kms_alias.source_default_key.target_key_arn]
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
        "arn:aws:s3:::${local.account.name}.backup.digideps.opg.service.justice.gov.uk/*",
      ]
    }

    resources = ["arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${local.account.s3_backup_kms_arn}"]
  }
}

// CREATE BACKUP ROLE USED FOR LOCAL AND CROSS ACCOUNT REPLICATION
resource "aws_iam_role" "backup_role" {
  name_prefix        = "digideps-backup-role"
  description        = "IAM Role for s3 replication in ${terraform.workspace}"
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
