locals {
  access_account_name = var.account_name == "development" ? "dev" : var.account_name
}

data "aws_region" "current" {}

data "aws_s3_bucket" "access_logging" {
  bucket = "s3-access-logs-opg-digideps-${local.access_account_name}-${data.aws_region.current.name}"
}
