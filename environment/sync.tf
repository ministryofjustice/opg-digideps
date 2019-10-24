data "aws_iam_role" "sync" {
  name = "sync"
}

data "aws_s3_bucket" "backup" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = "aws.management"
