variable "default_role" {
  default = "digideps-ci"
}

variable "accounts" {
  type = map(
    object({
      account_id            = string
      cloudtrail_bucket     = string
      cloudformation_bucket = string
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
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
  }
}
