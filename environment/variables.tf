variable "DEFAULT_ROLE" {
  default = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "accounts" {
  type = map(
    object({
      account_id              = string
      admin_allow_list        = list(string)
      force_destroy_bucket    = bool
      front_allow_list        = list(string)
      ga_default              = string
      ga_gds                  = string
      subdomain_enabled       = bool
      is_production           = number
      secrets_prefix          = string
      task_count              = number
      scan_count              = number
      symfony_env             = string
      db_subnet_group         = string
      ec_subnet_group         = string
      sirius_api_account      = string
      state_source            = string
      elasticache_count       = number
      always_on               = bool
      copy_version_from       = string
      cpu_low                 = number
      cpu_medium              = number
      cpu_high                = number
      memory_low              = number
      memory_medium           = number
      memory_high             = number
      backup_retention_period = number
    })
  )
}

data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/terraform-aws-moj-ip-whitelist.git"
}

locals {
  default_allow_list = concat(module.allow_list.moj_sites, formatlist("%s/32", data.aws_nat_gateway.nat[*].public_ip))

  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks

  environment      = lower(terraform.workspace)
  account          = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  subdomain        = local.account["subdomain_enabled"] ? local.environment : ""
  front_allow_list = length(local.account["front_allow_list"]) > 0 ? local.account["front_allow_list"] : local.default_allow_list
  admin_allow_list = length(local.account["admin_allow_list"]) > 0 ? local.account["admin_allow_list"] : local.default_allow_list
}

data "terraform_remote_state" "shared" {
  backend   = "s3"
  workspace = local.account.state_source
  config = {
    bucket   = "opg.terraform.state"
    key      = "digideps-infrastructure-shared/terraform.tfstate"
    region   = "eu-west-1"
    role_arn = "arn:aws:iam::311462405659:role/${var.DEFAULT_ROLE}"
  }
}
