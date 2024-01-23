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
      sirius_environment                     = string
      admin_allow_list                       = list(string)
      force_destroy_bucket                   = bool
      front_allow_list                       = list(string)
      ga_default                             = string
      ga_gds                                 = string
      subdomain_enabled                      = bool
      is_production                          = number
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
      fargate_spot                           = bool
    })
  )
}

data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git"
}

locals {
  account        = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  secrets_prefix = contains(keys(var.accounts), local.environment) ? local.environment : "default"
  environment    = lower(terraform.workspace)

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
