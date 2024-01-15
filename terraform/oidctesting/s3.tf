resource "aws_s3_bucket" "bucket" {
  bucket        = "opg-oidc-test-bucket-todel"
  force_destroy = true
}

resource "aws_s3_bucket_public_access_block" "public_access_policy" {
  bucket = aws_s3_bucket.bucket.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}
