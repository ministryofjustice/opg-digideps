# This can all go once all prod objects have expired
# as we are now logging to centrally set up access logs
resource "aws_s3_bucket" "s3_access_logs" {
  bucket        = "s3-access-logs.${local.environment}"
  force_destroy = local.account["force_destroy_bucket"]
}

resource "aws_s3_bucket_server_side_encryption_configuration" "s3_access_logs" {
  bucket = aws_s3_bucket.s3_access_logs.bucket

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_versioning" "s3_access_logs" {
  bucket = aws_s3_bucket.s3_access_logs.id
  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_acl" "s3_access_logs" {
  bucket = aws_s3_bucket.s3_access_logs.id
  acl    = "log-delivery-write"
}

resource "aws_s3_bucket_public_access_block" "s3_access_logs" {
  bucket = aws_s3_bucket.s3_access_logs.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_lifecycle_configuration" "s3_access_logs" {
  bucket = aws_s3_bucket.s3_access_logs.id

  rule {
    id     = "archive-after-30-days"
    status = "Enabled"

    transition {
      days          = 30
      storage_class = "GLACIER"
    }

    noncurrent_version_transition {
      noncurrent_days = 30
      storage_class   = "GLACIER"
    }
  }

  rule {
    id     = "expire-after-180-days"
    status = "Enabled"

    noncurrent_version_expiration {
      noncurrent_days = 180
    }

    expiration {
      days                         = 180
      expired_object_delete_marker = true
    }
  }
}
