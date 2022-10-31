variable "DEFAULT_ROLE" {
  default = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "accounts" {
  type = map(
    object({
      name                                   = string
      account_id                             = string
      admin_allow_list                       = list(string)
      force_destroy_bucket                   = bool
      front_allow_list                       = list(string)
      ga_default                             = string
      ga_gds                                 = string
      subdomain_enabled                      = bool
      is_production                          = number
      secrets_prefix                         = string
      task_count                             = number
      scan_count                             = number
      app_env                                = string
      db_subnet_group                        = string
      ec_subnet_group                        = string
      sirius_api_account                     = string
      state_source                           = string
      elasticache_count                      = number
      cpu_low                                = number
      cpu_medium                             = number
      cpu_high                               = number
      memory_low                             = number
      memory_medium                          = number
      memory_high                            = number
      backup_retention_period                = number
      psql_engine_version                    = string
      alarms_active                          = bool
      dr_backup                              = bool
      ecs_scale_min                          = number
      ecs_scale_max                          = number
      aurora_instance_count                  = number
      aurora_serverless                      = bool
      deletion_protection                    = bool
      aurora_enabled                         = bool
      s3_backup_replication                  = bool
      s3_backup_kms_arn                      = string
      associate_alb_with_waf_web_acl_enabled = bool
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
  project = "digideps"

  default_allow_list = concat(module.allow_list.moj_sites, formatlist("%s/32", data.aws_nat_gateway.nat[*].public_ip))
  admin_allow_list   = length(local.account["admin_allow_list"]) > 0 ? local.account["admin_allow_list"] : local.default_allow_list
  front_allow_list   = length(local.account["front_allow_list"]) > 0 ? local.account["front_allow_list"] : local.default_allow_list

  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks

  account     = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  environment = lower(terraform.workspace)

  # Have to account for disparity in env names w/ Sirius
  non_dev_environments     = ["production02", "preproduction", "training", "integration"]
  sirius_environment_check = contains(local.non_dev_environments, local.environment) ? local.environment : "dev"
  sirius_environment       = local.sirius_environment_check == "production02" ? "production" : local.sirius_environment_check

  subdomain               = local.account["subdomain_enabled"] ? local.environment : ""
  backup_account_id       = "238302996107"
  cross_account_role_name = "cross-acc-db-backup.digideps-production"

  default_tags = {
    business-unit          = "OPG"
    application            = "Digideps"
    environment-name       = local.environment
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.is_production
  }
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
