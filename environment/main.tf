locals {
  project = "digi-deps"

  default_tags = {
    business-unit          = "OPG"
    application            = "Digi-Deps"
    environment-name       = terraform.workspace
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.is_production
  }

  db_subnet_group = "rds-private-subnets-${local.vpc_name}"
  ec_subnet_group = "ec-pvt-subnets-${local.vpc_name}"
}

data "aws_acm_certificate" "external" {
  domain      = local.external_certificate_name
  types       = ["AMAZON_ISSUED"]
  most_recent = true
}
