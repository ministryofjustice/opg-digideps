variable "default_tags" {
  type        = any
  description = "The default tags to use"
}

variable "account" {
  type        = any
  description = "The account map"
}

variable "default_role" {
  type        = string
  description = "The default role to use"
}

variable "docker_tag" {
  type        = string
  description = "The docker_tag"
}

variable "secrets_prefix" {
  type        = string
  description = "The account map"
}

variable "canonical_user_ids" {
  type        = any
  description = "The map of canonical user ids"
}

variable "r53_hc_front" {
  type = any
}

variable "r53_hc_admin" {
  type = any
}

variable "certificate_arn" {
  type = string
}

variable "front_fqdn" {
  type = string
}

variable "admin_fqdn" {
  type = string
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git"
}

data "aws_ip_ranges" "route53_healthchecks_ips" {
  services = ["route53_healthchecks"]
}

locals {
  project = "digideps"

  default_allow_list = concat(module.allow_list.moj_sites, formatlist("%s/32", data.aws_nat_gateway.nat[*].public_ip))
  admin_allow_list   = length(var.account["admin_allow_list"]) > 0 ? var.account["admin_allow_list"] : local.default_allow_list
  front_allow_list   = length(var.account["front_allow_list"]) > 0 ? var.account["front_allow_list"] : local.default_allow_list

  backup_account_id       = "238302996107"
  cross_account_role_name = "cross-acc-db-backup.digideps-production"

  route53_healthchecker_ips = data.aws_ip_ranges.route53_healthchecks_ips.cidr_blocks

  environment = lower(terraform.workspace)

  sirius_environment = var.account["sirius_environment"]

  openapi_mock_version = "v0.3.3"

  capacity_provider = var.account.fargate_spot ? "FARGATE_SPOT" : "FARGATE"
}

data "terraform_remote_state" "shared" {
  backend   = "s3"
  workspace = var.account.state_source
  config = {
    bucket   = "opg.terraform.state"
    key      = "digideps-infrastructure-shared/terraform.tfstate"
    region   = "eu-west-1"
    role_arn = "arn:aws:iam::311462405659:role/${var.default_role}"
  }
}
