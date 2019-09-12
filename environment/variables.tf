variable "default_role" {
  default = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "accounts" {
  type = map(
    object({
      account_id             = string
      admin_whitelist        = list(string)
      email_feedback_address = string
      email_report_address   = string
      email_update_address   = string
      force_destroy_bucket   = bool
      front_whitelist        = list(string)
      host_suffix_enabled    = bool
      is_production          = number
      secrets_prefix         = string
      task_count             = number
      test_enabled           = bool
      vpc_name               = string
      db_subnet_group        = string
      ec_subnet_group        = string
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
  environment     = lower(terraform.workspace)
  account         = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  host_suffix     = local.account["host_suffix_enabled"] ? local.environment : ""
  front_whitelist = length(local.account["front_whitelist"]) > 0 ? local.account["front_whitelist"] : local.default_whitelist
  admin_whitelist = length(local.account["admin_whitelist"]) > 0 ? local.account["admin_whitelist"] : local.default_whitelist
}
