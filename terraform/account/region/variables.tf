variable "account" {
  description = "The account map built from tfvars"
}

variable "default_tags" {
  description = "Default tags map"
}

data "aws_region" "current" {}

data "aws_caller_identity" "current" {}

locals {
  current_main_region = data.aws_region.current.name
  s3_bucket           = var.account.name == "production" ? "${var.account.name}02" : var.account.name
}
