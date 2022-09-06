locals {
  access_account_name = var.account_name == "preproduction" ? "preprod" : var.account_name
}

data "aws_region" "current" {}

data "aws_s3_bucket" "access_logging" {
  bucket = "s3-access-logs-opg-opg-use-an-lpa-${local.access_account_name}-${data.aws_region.current.name}"
}
