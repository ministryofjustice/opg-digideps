variable "DEFAULT_ROLE" {
  default = "digideps-ci"
}

variable "accounts" {
  type = map(
    object({
      account_id        = string
      name              = string
      db_subnet_group   = string
      ec_subnet_group   = string
      environments      = set(string)
      sirius_account_id = string
    })
  )
}

locals {
  account = var.accounts[terraform.workspace]

  default_tags = {
    business-unit          = "OPG"
    application            = "Digideps"
    environment-name       = terraform.workspace
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam+digideps@digital.justice.gov.uk"
    is-production          = local.account.name == "production" ? true : false
  }

  s3_bucket = local.account.name == "production" ? "${local.account.name}02" : local.account.name

  cloud9_users = [
    "alex.saunders",
    "gugandeep.chani",
    "stephen.hinett",
  ]
}
