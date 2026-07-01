variable "account" {
  description = "The account map built from tfvars"
  type        = any
}

variable "default_tags" {
  description = "Default tags map"
  type        = any
}

data "aws_region" "current" {}

data "aws_caller_identity" "current" {}

locals {
  current_main_region = data.aws_region.current.name
  s3_bucket           = var.account.name
  default_allow_list  = concat(module.allow_list.palo_alto_prisma_access, module.allow_list.moj_sites)
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git?ref=v3.0.3"
}
