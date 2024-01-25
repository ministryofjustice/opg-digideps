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
      secondary_region_enabled               = bool
    })
  )
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git"
}

data "aws_canonical_user_id" "development" {
  provider = aws.development
}

data "aws_canonical_user_id" "preproduction" {
  provider = aws.preproduction
}

data "aws_canonical_user_id" "production" {
  provider = aws.production
}

locals {
  primary_region = "eu-west-1"
  account        = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  secrets_prefix = contains(keys(var.accounts), local.environment) ? local.environment : "default"
  subdomain      = local.account["subdomain_enabled"] ? local.environment : ""

  environment = lower(terraform.workspace)

  default_tags = {
    business-unit          = "OPG"
    application            = "Digideps"
    environment-name       = local.environment
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.is_production
  }

  canonical_user_ids = {
    development   = data.aws_canonical_user_id.development.id
    preproduction = data.aws_canonical_user_id.preproduction.id
    production    = data.aws_canonical_user_id.production.id
  }
}
