# these buckets are created & managed by #security
# we just ensure they're not publicly accessible

resource "aws_s3_bucket" "cloudformation" {
  bucket = local.account.cloudformation_bucket

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "AES256"
      }
    }
  }

}

resource "aws_s3_bucket_public_access_block" "cloudformation_bucket" {
  bucket = aws_s3_bucket.cloudformation.bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}
