locals {
  project = "digideps"

  default_tags = {
    business-unit          = "OPG"
    application            = "Digideps"
    environment-name       = local.environment
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.is_production
  }
}
