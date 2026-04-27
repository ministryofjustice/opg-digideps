variable "default_tags" {
  type        = any
  description = "The default tags to use"
}

variable "account" {
  type        = any
  description = "The account map"
}

variable "docker_tag" {
  type        = string
  description = "The docker_tag"
}

variable "secrets_prefix" {
  type        = string
  description = "The account map"
}

variable "shared_environment_variables" {
  type        = any
  description = "The map of shared environment variables"
}

variable "health_check_front" {
  type = any
}

variable "health_check_admin" {
  type = any
}

variable "certificate_arn" {
  type = string
}

variable "complete_deputy_report_cert_arn" {
  type = string
}

variable "front_fully_qualified_domain_name" {
  type = string
}

variable "admin_fully_qualified_domain_name" {
  type = string
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git?ref=v3.0.3"
}

data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

locals {
  default_allow_list = concat(module.allow_list.palo_alto_prisma_access, module.allow_list.moj_sites, formatlist("%s/32", data.aws_nat_gateway.nat_gateway[*].public_ip))
  admin_allow_list   = length(var.account["dns"]["admin_allow_list"]) > 0 ? var.account["dns"]["admin_allow_list"] : local.default_allow_list
  front_allow_list   = length(var.account["dns"]["front_allow_list"]) > 0 ? var.account["dns"]["front_allow_list"] : local.default_allow_list

  backup_account_id       = "238302996107"
  cross_account_role_name = var.account.environment.name == "production" ? "cross-acc-db-backup.digideps-production" : "cross-acc-db-backup.digideps-development"

  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks

  environment = lower(terraform.workspace)

  capacity_provider = var.account.ecs.fargate_spot ? "FARGATE_SPOT" : "FARGATE"

  pa_pro_report_csv_filename  = "paProDeputyReport.csv"
  lay_report_csv_file         = "layDeputyReport.csv"
  deputyships_report_csv_file = "deputyshipsReport.csv"


  is_pr_environment = !contains([
    "development",
    "preproduction",
    "production",
    "staging"
  ], local.environment)

  wait_for_ecs_steady_state = !local.is_pr_environment
  # DNS switch variables
  certificate_arn = var.certificate_arn == "" ? data.aws_acm_certificate.service_justice.arn : var.certificate_arn

  use_new_db    = local.environment == "change_to_pre_and_prod" ? true : false
  create_new_db = local.environment == "preproduction" ? true : false
  # create_new_db = local.environment == "preproduction" || local.environment == "production" ? true: false
}
