variable "default_role" {
  default = "digideps-ci"
}

variable "management_role" {
  default = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "accounts" {
  type = map(
    object({
      account_id                = string
      account_long_name         = string
      account_name              = string
      admin_prefix              = string
      admin_whitelist           = list(string)
      domain                    = string
      domain_name               = string
      email_domain              = string
      email_feedback_address    = string
      email_report_address      = string
      email_update_address      = string
      external_certificate_name = string
      front_prefix              = string
      front_whitelist           = list(string)
      host_suffix_enabled       = bool
      is_production             = number
      test_enabled              = bool
      task_count                = number
      vpc_name                  = string
      secrets_prefix            = string
    })
  )
}

locals {
  default_whitelist = [
    "157.203.176.138/32",
    "157.203.176.139/32",
    "157.203.176.140/32",
    "157.203.177.190/32",
    "157.203.177.191/32",
    "157.203.177.192/32",
    "194.33.192.0/25",
    "194.33.193.0/25",
    "194.33.196.0/25",
    "194.33.197.0/25",
    "195.59.75.0/24",
    "195.99.201.194/32",
    "213.121.161.124/32",
    "213.121.252.154/32",
    "34.249.23.21/32",
    "52.210.230.211/32",
    "52.215.20.165/32",
    "52.30.28.165/32",
    "54.77.122.216/32",
    "62.25.109.201/32",
    "62.25.109.203/32",
    "81.134.202.29/32",
    "94.30.9.148/32",
  ]

  account         = contains(keys(var.accounts), terraform.workspace) ? var.accounts[terraform.workspace] : var.accounts["default"]
  host_suffix     = local.account["host_suffix_enabled"] ? terraform.workspace : ""
  front_whitelist = length(local.account["front_whitelist"]) > 0 ? local.account["front_whitelist"] : local.default_whitelist
  admin_whitelist = length(local.account["admin_whitelist"]) > 0 ? local.account["admin_whitelist"] : local.default_whitelist
}
