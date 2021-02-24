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
