variable "DEFAULT_ROLE" {
  type        = string
  description = "Default role to use for providers"
  default     = "digideps-ci"
}

variable "DEFAULT_ROLE_MGMT" {
  type        = string
  description = "Default role to use for management providers"
  default     = "digideps-custom-ci"
}

variable "accounts" {
  type = map(
    object({
      account_id         = string
      name               = string
      ip_block_workspace = string
      db_subnet_group    = string
      ec_subnet_group    = string
      environments       = set(string)
      dns_firewall = object({
        enabled         = bool
        domains_allowed = list(string)
        domains_blocked = list(string)
      })
      sirius_account_id        = string
      apply_immediately        = bool
      secondary_region_enabled = bool
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
}
