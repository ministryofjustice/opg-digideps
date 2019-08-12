locals {
  project = "digi-deps"

  default_tags = {
    business-unit          = "OPG"
    application            = "Digi-Deps"
    environment-name       = local.environment
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.is_production
  }

  db_subnet_group = "rds-private-subnets-${local.account.vpc_name}"
  ec_subnet_group = "ec-pvt-subnets-${local.account.vpc_name}"
}

data "aws_acm_certificate" "external" {
  domain      = local.account.external_certificate_name
  types       = ["AMAZON_ISSUED"]
  most_recent = true
}
