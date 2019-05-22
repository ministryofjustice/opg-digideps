variable "default_role" {
  default = "digideps-ci"
}

variable "management_role" {
  default = "digideps-ci"
}

variable "OPG_DOCKER_TAG" {
  description = "docker tag to deploy"
}

variable "account_ids" {
  type = "map"
}

variable "account_long_names" {
  type = "map"
}

variable "account_names" {
  type = "map"
}

variable "admin_prefixes" {
  type = "map"
}

variable "admin_whitelists" {
  type = "map"
}

variable "domains" {
  type = "map"
}

variable "email_domains" {
  type = "map"
}

variable "email_feedback_addresses" {
  type = "map"
}

variable "email_report_addresses" {
  type = "map"
}

variable "email_update_addresses" {
  type = "map"
}

variable "external_certificate_names" {
  type = "map"
}

variable "front_whitelists" {
  type = "map"
}

variable "front_prefixes" {
  type = "map"
}

variable "host_suffix" {
  type = "map"
}

variable "is_production" {
  type = "map"
}

variable "maintenance_enabled" {
  type = "map"
}

variable "task_count" {
  type = "map"
}

variable "test_enabled" {
  type = "map"
}

variable "vpc_enabled" {
  type = "map"
}

variable "vpc_names" {
  type = "map"
}

locals {
  account_id                = "${lookup(var.account_ids, terraform.workspace )}"
  account_long_name         = "${lookup(var.account_long_names, terraform.workspace )}"
  account_name              = "${lookup(var.account_names, terraform.workspace )}"
  admin_prefix              = "${lookup(var.admin_prefixes, terraform.workspace )}"
  admin_whitelist           = "${var.admin_whitelists[terraform.workspace]}"
  domain                    = "${lookup(var.domains, terraform.workspace )}"
  domain_name               = "dd.opg.digital"
  email_domain              = "${lookup(var.email_domains, terraform.workspace )}"
  email_feedback_address    = "${lookup(var.email_feedback_addresses, terraform.workspace )}"
  email_report_address      = "${lookup(var.email_report_addresses, terraform.workspace )}"
  email_update_address      = "${lookup(var.email_update_addresses, terraform.workspace )}"
  external_certificate_name = "${var.external_certificate_names[terraform.workspace]}"
  front_prefix              = "${lookup(var.front_prefixes, terraform.workspace )}"
  front_whitelist           = "${var.front_whitelists[terraform.workspace]}"
  host_suffix               = "${lookup(var.host_suffix, terraform.workspace )}"
  is_production             = "${lookup(var.is_production, terraform.workspace )}"
  maintenance_enabled       = "${lookup(var.maintenance_enabled, terraform.workspace )}"
  test_enabled              = "${lookup(var.test_enabled, terraform.workspace )}"
  task_count                = "${lookup(var.task_count, terraform.workspace )}"
  vpc_enabled               = "${lookup(var.vpc_enabled, terraform.workspace )}"
  vpc_name                  = "${lookup(var.vpc_names, terraform.workspace )}"
}
