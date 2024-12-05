#tfsec:ignore:aws-s3-enable-bucket-logging - only contains dev data. no need for access logging
resource "aws_s3_bucket" "pa_uploads_branch_replication" {
  count         = var.account.name == "development" ? 1 : 0
  bucket        = "pa-uploads-branch-replication"
  force_destroy = true
  tags          = var.default_tags
}

resource "aws_s3_bucket_public_access_block" "pa_uploads_branch_replication" {
  count  = var.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket.pa_uploads_branch_replication[0].bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_policy" "pa_uploads_branch_replication" {
  count  = var.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket_public_access_block.pa_uploads_branch_replication[0].bucket
  policy = data.aws_iam_policy_document.pa_uploads_branch_replication[0].json
}

data "aws_iam_policy_document" "pa_uploads_branch_replication" {
  count     = var.account.name == "development" ? 1 : 0
  policy_id = "PutObjPolicy"

  statement {
    sid    = "DenyUnEncryptedObjectUploads"
    effect = "Deny"

    principals {
      identifiers = ["*"]
      type        = "AWS"
    }

    actions   = ["s3:PutObject"]
    resources = ["${aws_s3_bucket.pa_uploads_branch_replication[0].arn}/*"]

    condition {
      test     = "StringNotEquals"
      values   = ["AES256"]
      variable = "s3:x-amz-server-side-encryption"
    }
  }
}

resource "aws_iam_role" "replication" {
  count              = var.account.name == "development" ? 1 : 0
  name               = "replication-role.replication"
  assume_role_policy = data.aws_iam_policy_document.assume_policy.json
  tags = merge(
    var.default_tags,
    { Name = "replication-role-${var.account.name}" },
  )
}

data "aws_iam_policy_document" "assume_policy" {
  statement {
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["s3.amazonaws.com"]
    }
    actions = ["sts:AssumeRole"]
  }
}

data "aws_iam_policy_document" "replication_policy" {
  count = var.account.name == "development" ? 1 : 0
  statement {
    sid    = "AllowReplication"
    effect = "Allow"
    actions = [
      "s3:GetReplicationConfiguration",
      "s3:ListBucket"
    ]
    resources = [
      aws_s3_bucket.pa_uploads_branch_replication[0].arn
    ]
  }

  statement {
    sid    = "GetObjectVersions"
    effect = "Allow"
    actions = [
      "s3:GetObjectVersion",
      "s3:GetObjectVersionAcl"
    ]
    resources = [
      "${aws_s3_bucket.pa_uploads_branch_replication[0].arn}/*"
    ]
  }
}

resource "aws_iam_policy" "replication" {
  count  = var.account.name == "development" ? 1 : 0
  name   = "replication-policy.replication"
  policy = data.aws_iam_policy_document.replication_policy[0].json
}

resource "aws_iam_role_policy_attachment" "replication" {
  count      = var.account.name == "development" ? 1 : 0
  role       = aws_iam_role.replication[0].name
  policy_arn = aws_iam_policy.replication[0].arn
}

resource "aws_s3_bucket_server_side_encryption_configuration" "pa_uploads_branch_replication" {
  count  = var.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket.pa_uploads_branch_replication[0].bucket

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_versioning" "pa_uploads_branch_replication" {
  count  = var.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket.pa_uploads_branch_replication[0].id
  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_ownership_controls" "pa_uploads_branch_replication" {
  count  = var.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket.pa_uploads_branch_replication[0].id
  rule {
    object_ownership = "BucketOwnerEnforced"
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "pa_uploads_branch_replication" {
  count  = var.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket.pa_uploads_branch_replication[0].id

  rule {
    id     = "expire-after-10-days"
    status = "Enabled"

    noncurrent_version_expiration {
      noncurrent_days = 10
    }

    expiration {
      days                         = 10
      expired_object_delete_marker = true
    }
  }
}
