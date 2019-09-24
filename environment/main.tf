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
}
