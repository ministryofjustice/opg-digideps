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

moved {
  from = aws_s3_bucket.pa_uploads
  to   = module.pa_uploads.aws_s3_bucket.bucket
}

moved {
  from = aws_s3_bucket_public_access_block.pa_uploads
  to   = module.pa_uploads.aws_s3_bucket_public_access_block.public_access_policy
}

moved {
  from = aws_iam_policy_document.pa_uploads
  to   = module.pa_uploads.data.aws_iam_policy_document.bucket
}

moved {
  from = aws_s3_bucket_policy.pa_uploads
  to   = module.pa_uploads.aws_s3_bucket_policy.bucket
}

module "pa_uploads" {
  source = "./s3_bucket"

  account_name                         = local.account.name
  bucket_name                          = "pa-uploads-${local.environment}"
  force_destroy                        = local.account.force_destroy_bucket
  kms_key_id                           = aws_kms_key.s3.key_id
  environment_name                     = local.environment
  enable_lifecycle                     = true
  expiration_days                      = local.expiration_days
  non_current_expiration_days          = local.noncurrent_expiration_days
  replication_within_account           = true
  replication_within_account_bucket    = data.aws_s3_bucket.replication_bucket.arn
  replication_to_backup                = false
  replication_to_backup_account_bucket = "arn:aws:s3:::${local.account.name}.backup.digideps.opg.service.justice.gov.uk"
  replication_role_arn                 = aws_iam_role.backup_role.arn
  replication_kms_key_id               = "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${local.account.s3_backup_kms_arn}"
  replication_account_id               = local.backup_account_id

  providers = {
    aws = aws
  }
}

// CREATE THE MAIN BUCKET
#resource "aws_s3_bucket" "pa_uploads" {
#  bucket        = "pa-uploads-${local.environment}"
#  acl           = "private"
#  force_destroy = local.account["force_destroy_bucket"]
#
#  versioning {
#    enabled = true
#  }
#
#  logging {
#    target_bucket = aws_s3_bucket.s3_access_logs.id
#    target_prefix = "pa_uploads/"
#  }
#
#  lifecycle_rule {
#    enabled = true
#
#    expiration {
#      days = local.expiration_days
#    }
#
#    noncurrent_version_expiration {
#      days = local.noncurrent_expiration_days
#    }
#  }
#
#  server_side_encryption_configuration {
#    rule {
#      apply_server_side_encryption_by_default {
#        sse_algorithm = "AES256"
#      }
#    }
#  }
#
#  replication_configuration {
#    role = aws_iam_role.backup_role.arn
#
#    rules {
#      id       = "ReplicationCrossAccount"
#      priority = 1
#      status   = local.account.s3_backup_replication
#
#      filter {}
#
#      destination {
#        account_id         = local.backup_account_id
#        bucket             = "arn:aws:s3:::${local.account.name}.backup.digideps.opg.service.justice.gov.uk"
#        replica_kms_key_id = "arn:aws:kms:eu-west-1:${local.backup_account_id}:key/${local.account.s3_backup_kms_arn}"
#
#        access_control_translation {
#          owner = "Destination"
#        }
#      }
#
#      source_selection_criteria {
#        sse_kms_encrypted_objects {
#          enabled = true
#        }
#      }
#    }
#    rules {
#      id       = "ReplicationLocalDevelopment"
#      priority = 2
#      status   = local.bucket_replication_status
#
#      filter {}
#
#      destination {
#        bucket        = data.aws_s3_bucket.replication_bucket.arn
#        storage_class = "STANDARD"
#      }
#    }
#  }
#  tags = local.default_tags
#}

// BLOCK PUBLIC ACCESS ON PA_UPLOADS
#resource "aws_s3_bucket_public_access_block" "pa_uploads" {
#  bucket = aws_s3_bucket.pa_uploads.bucket
#
#  block_public_acls       = true
#  block_public_policy     = true
#  ignore_public_acls      = true
#  restrict_public_buckets = true
#}

// BASE POLICY FOR PA_UPLOADS
#data "aws_iam_policy_document" "pa_uploads" {
#  policy_id = "PutObjPolicy"
#
#  statement {
#    sid    = "DenyUnEncryptedObjectUploads"
#    effect = "Deny"
#
#    principals {
#      identifiers = ["*"]
#      type        = "AWS"
#    }
#
#    actions   = ["s3:PutObject"]
#    resources = ["${aws_s3_bucket.pa_uploads.arn}/*"]
#
#    condition {
#      test     = "StringNotEquals"
#      values   = ["AES256"]
#      variable = "s3:x-amz-server-side-encryption"
#    }
#  }
#
#  statement {
#    sid    = "DenyNoneSSLRequests"
#    effect = "Deny"
#    actions = [
#    "s3:*"]
#    resources = [
#      aws_s3_bucket.pa_uploads.arn,
#      "${aws_s3_bucket.pa_uploads.arn}/*"
#    ]
#
#    condition {
#      test     = "Bool"
#      variable = "aws:SecureTransport"
#      values = [
#      false]
#    }
#
#    principals {
#      type        = "AWS"
#      identifiers = ["*"]
#    }
#  }
#
#  statement {
#    sid     = "DelegateS3Access"
#    effect  = "Allow"
#    actions = ["s3:ListBucket", "s3:GetObject"]
#
#    principals {
#      type        = "AWS"
#      identifiers = ["arn:aws:iam::${local.backup_account_id}:root"]
#    }
#
#    resources = [
#      aws_s3_bucket.pa_uploads.arn,
#      "${aws_s3_bucket.pa_uploads.arn}/*"
#    ]
#  }
#}

// ATTACH BASE POLICY FOR PA_UPLOADS
#resource "aws_s3_bucket_policy" "pa_uploads" {
#  bucket = aws_s3_bucket_public_access_block.pa_uploads.bucket
#  policy = data.aws_iam_policy_document.pa_uploads.json
#}
