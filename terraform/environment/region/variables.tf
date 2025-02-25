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

variable "state_role" {
  type = string
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git?ref=v3.0.3"
}

data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

locals {
  default_allow_list = concat(module.allow_list.palo_alto_prisma_access, module.allow_list.moj_sites, formatlist("%s/32", data.aws_nat_gateway.nat[*].public_ip))
  admin_allow_list   = length(var.account["admin_allow_list"]) > 0 ? var.account["admin_allow_list"] : local.default_allow_list
  front_allow_list   = length(var.account["front_allow_list"]) > 0 ? var.account["front_allow_list"] : local.default_allow_list

  backup_account_id       = "238302996107"
  cross_account_role_name = var.account.name == "production" ? "cross-acc-db-backup.digideps-production" : "cross-acc-db-backup.digideps-development"

  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks

  environment = lower(terraform.workspace)

  openapi_mock_version = "v0.3.3"

  capacity_provider = var.account.fargate_spot ? "FARGATE_SPOT" : "FARGATE"

  pa_pro_report_csv_filename  = "paProDeputyReport.csv"
  lay_report_csv_file         = "layDeputyReport.csv"
  court_order_report_csv_file = "courtOrdersReport.csv"
}

data "terraform_remote_state" "shared" {
  backend   = "s3"
  workspace = var.account.state_source
  config = {
    bucket = "opg.terraform.state"
    key    = "opg-digideps-account/terraform.tfstate"
    region = "eu-west-1"
    assume_role = {
      role_arn = "arn:aws:iam::311462405659:role/${var.state_role}"
    }
  }
}
