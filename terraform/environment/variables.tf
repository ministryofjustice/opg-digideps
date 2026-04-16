variable "DEFAULT_ROLE" {
  type        = string
  description = "Default role to use for providers"
  default     = "digideps-ci"
}

variable "MANAGEMENT_ROLE" {
  type        = string
  description = "Management role to use for providers"
  default     = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
  type        = string
}

variable "accounts" {
  type = map(
    object({
      environment = object({
        name                                = string
        account_id                          = string
        app_env                             = string
        is_production                       = number
        fixtures_enabled                    = bool
        alarms_active                       = bool
        fault_injection_experiments_enabled = bool
        sleep_mode_enabled                  = bool
        secondary_region_enabled            = bool
        run_one_off_migrations              = string
      })
      sirius = object({
        environment = string
        account     = string
      })
      dns = object({
        subdomain_enabled = bool
        front_allow_list  = list(string)
        admin_allow_list  = list(string)
      })
      ecs = object({
        scale_min    = number
        scale_max    = number
        task_count   = number
        scan_count   = number
        cpu_low      = string
        memory_low   = string
        memory_high  = string
        fargate_spot = bool
      })
      db = object({
        aurora_instance_count   = number
        aurora_serverless       = bool
        dr_backup               = bool
        backup_retention_period = number
        deletion_protection     = bool
        psql_engine_version     = string
        min_acu                 = number
        max_acu                 = number
      })
      s3 = object({
        backup_kms_arn       = string
        backup_replication   = bool
        force_destroy_bucket = bool
      })
      waf = object({
        associate_alb_with_waf_web_acl_enabled = bool
        waf_ip_blocking_enabled                = bool
      })
    })
  )
}

data "aws_ssm_parameter" "env_vars_development" {
  provider = aws.management_eu_west_1
  name     = "/digideps/development/environment_variables"
}

data "aws_ssm_parameter" "env_vars_preproduction" {
  provider = aws.management_eu_west_1
  name     = "/digideps/preproduction/environment_variables"
}

data "aws_ssm_parameter" "env_vars_production" {
  provider = aws.management_eu_west_1
  name     = "/digideps/production/environment_variables"
}

locals {
  primary_region_name = "eu-west-1"
  account             = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  secrets_prefix      = contains(keys(var.accounts), local.environment) ? local.environment : "default"
  subdomain           = local.account.dns.subdomain_enabled ? local.environment : ""

  environment = lower(terraform.workspace)

  default_tags = {
    business-unit          = "OPG"
    application            = "Digideps"
    environment-name       = local.environment
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.environment.is_production
  }

  shared_environment_variables = {
    canonical_id_development   = jsondecode(nonsensitive(data.aws_ssm_parameter.env_vars_development.value))["canonical_user_id"]
    canonical_id_preproduction = jsondecode(nonsensitive(data.aws_ssm_parameter.env_vars_preproduction.value))["canonical_user_id"]
    canonical_id_production    = jsondecode(nonsensitive(data.aws_ssm_parameter.env_vars_production.value))["canonical_user_id"]
    replication_bucket         = jsondecode(nonsensitive(data.aws_ssm_parameter.env_vars_development.value))["replication_bucket"]
  }

  # DNS Switchover variables
  complete_deputy_dns_enabled = local.account.environment.name == "development" ? 0 : 1
  main_cert                   = local.complete_deputy_dns_enabled == 1 ? aws_acm_certificate_validation.wildcard[0].certificate_arn : ""
  new_cert                    = local.complete_deputy_dns_enabled == 1 ? aws_acm_certificate_validation.complete_deputy_report_wildcard[0].certificate_arn : ""
  dns_account                 = local.complete_deputy_dns_enabled == 1 ? "515688267891" : local.account.environment.account_id
}
