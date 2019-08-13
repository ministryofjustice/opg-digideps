resource "aws_s3_bucket" "pa_uploads" {
  bucket = "pa-uploads-${local.environment}"
  acl    = "private"
  force_destroy = local.account["force_destroy_bucket"]

  versioning {
    enabled = true
  }

  lifecycle_rule {
    enabled = true

    expiration {
      days = 490
    }

    noncurrent_version_expiration {
      days = 10
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
