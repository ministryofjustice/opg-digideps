variable "default_role" {
  default = "ci"
}

variable "management_role" {
  default = "ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "account_ids" {
  type = map(string)
}

variable "account_long_names" {
  type = map(string)
}

variable "account_names" {
  type = map(string)
}

variable "admin_prefixes" {
  type = map(string)
}

variable "admin_whitelists" {
  type = map(list(string))
}

variable "domains" {
  type = map(string)
}

variable "email_domains" {
  type = map(string)
}

variable "email_feedback_addresses" {
  type = map(string)
}

variable "email_report_addresses" {
  type = map(string)
}

variable "email_update_addresses" {
  type = map(string)
}

variable "external_certificate_names" {
  type = map(string)
}

variable "front_whitelists" {
  type = map(list(string))
}

variable "front_prefixes" {
  type = map(string)
}

variable "host_suffix" {
  type = map(string)
}

variable "is_production" {
  type = map(string)
}

variable "task_count" {
  type = map(string)
}

variable "test_enabled" {
  type = map(string)
}

variable "vpc_names" {
  type = map(string)
}

locals {
  account_id                = var.account_ids[terraform.workspace]
  account_long_name         = var.account_long_names[terraform.workspace]
  account_name              = var.account_names[terraform.workspace]
  admin_prefix              = var.admin_prefixes[terraform.workspace]
  admin_whitelist           = var.admin_whitelists[terraform.workspace]
  domain                    = var.domains[terraform.workspace]
  domain_name               = "dd.opg.digital"
  email_domain              = var.email_domains[terraform.workspace]
  email_feedback_address    = var.email_feedback_addresses[terraform.workspace]
  email_report_address      = var.email_report_addresses[terraform.workspace]
  email_update_address      = var.email_update_addresses[terraform.workspace]
  external_certificate_name = var.external_certificate_names[terraform.workspace]
  front_prefix              = var.front_prefixes[terraform.workspace]
  front_whitelist           = var.front_whitelists[terraform.workspace]
  host_suffix               = var.host_suffix[terraform.workspace]
  is_production             = var.is_production[terraform.workspace] ? 1 : 0
  test_enabled              = var.test_enabled[terraform.workspace]
  task_count                = var.task_count[terraform.workspace]
  vpc_name                  = var.vpc_names[terraform.workspace]
}

