variable "DEFAULT_ROLE" {
  default = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "accounts" {
  type = map(
    object({
      account_id           = string
      admin_whitelist      = list(string)
      force_destroy_bucket = bool
      front_whitelist      = list(string)
      ga_default           = string
      ga_gds               = string
      subdomain_enabled    = bool
      is_production        = number
      secrets_prefix       = string
      task_count           = number
      mock_emails          = bool
      symfony_env          = string
      db_subnet_group      = string
      ec_subnet_group      = string
      state_source         = string
      db_serverless        = bool
    })
  )
}

data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

module "whitelist" {
  source = "git@github.com:ministryofjustice/terraform-aws-moj-ip-whitelist.git"
}

locals {
  default_whitelist = concat(module.whitelist.moj_sites, formatlist("%s/32", data.aws_nat_gateway.nat[*].public_ip))

  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks

  environment     = lower(terraform.workspace)
  account         = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  subdomain       = local.account["subdomain_enabled"] ? local.environment : ""
  front_whitelist = length(local.account["front_whitelist"]) > 0 ? local.account["front_whitelist"] : local.default_whitelist
  admin_whitelist = length(local.account["admin_whitelist"]) > 0 ? local.account["admin_whitelist"] : local.default_whitelist
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
