locals {
  non-replication_workspaces = ["production02", "preproduction", "training", "integration", "development"]
  bucket_replication_status  = contains(local.non-replication_workspaces, local.environment) ? "Disabled" : "Enabled"
  long_expiry_workspaces     = ["production02", "development", "training"]
  expiration_days            = contains(local.long_expiry_workspaces, local.environment) ? 490 : 14
  noncurrent_expiration_days = contains(local.long_expiry_workspaces, local.environment) ? 365 : 7
}

data "aws_s3_bucket" "replication_bucket" {
  bucket   = "pa-uploads-branch-replication"
  provider = aws.development
}

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
    role = aws_iam_role.replication.arn

    rules {
      status = local.bucket_replication_status

      destination {
        bucket        = data.aws_s3_bucket.replication_bucket.arn
        storage_class = "STANDARD"
      }
    }
  }

  tags = local.default_tags
}

resource "aws_s3_bucket_public_access_block" "pa_uploads" {
  bucket = aws_s3_bucket.pa_uploads.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_policy" "pa_uploads" {
  bucket = aws_s3_bucket_public_access_block.pa_uploads.bucket
  policy = data.aws_iam_policy_document.pa_uploads.json
}

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
}

resource "aws_iam_role" "replication" {
  name = "replication-role.${local.environment}"
  tags = local.default_tags

  assume_role_policy = <<POLICY
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": "sts:AssumeRole",
      "Principal": {
        "Service": "s3.amazonaws.com"
      },
      "Effect": "Allow",
      "Sid": ""
    }
  ]
}
POLICY
}

resource "aws_iam_policy" "replication" {
  name = "replication-policy.${local.environment}"

  policy = <<POLICY
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "s3:GetReplicationConfiguration",
        "s3:ListBucket"
      ],
      "Effect": "Allow",
      "Resource": [
        "${aws_s3_bucket.pa_uploads.arn}"
      ]
    },
    {
      "Action": [
        "s3:GetObjectVersion",
        "s3:GetObjectVersionAcl"
      ],
      "Effect": "Allow",
      "Resource": [
        "${aws_s3_bucket.pa_uploads.arn}/*"
      ]
    },
    {
      "Action": [
        "s3:ReplicateObject",
        "s3:ReplicateDelete"
      ],
      "Effect": "Allow",
      "Resource": "${data.aws_s3_bucket.replication_bucket.arn}/*"
    }
  ]
}
POLICY
}

resource "aws_iam_role_policy_attachment" "replication" {
  role       = aws_iam_role.replication.name
  policy_arn = aws_iam_policy.replication.arn
}

resource "aws_s3_bucket" "s3_access_logs" {
  bucket        = "s3-access-logs.${local.environment}"
  acl           = "log-delivery-write"
  force_destroy = local.account["force_destroy_bucket"]

  versioning {
    enabled = true
  }

  lifecycle_rule {
    transition {
      days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_transition {
      days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_expiration {
      days = 180
    }

    expiration {
      days                         = 180
      expired_object_delete_marker = true
    }

    enabled = true
  }

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "AES256"
      }
    }
  }
}

resource "aws_s3_bucket_public_access_block" "s3_access_logs" {
  bucket = aws_s3_bucket.s3_access_logs.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}
