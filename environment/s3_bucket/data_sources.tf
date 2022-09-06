data "aws_region" "current" {}

data "aws_s3_bucket" "access_logging" {
  bucket = "s3-access-logs.${var.environment_name}"
}
