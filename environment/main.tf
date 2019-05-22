locals {
  project = "digi-deps"

  default_tags = {
    business-unit          = "OPG"
    application            = "Digi-Deps"
    environment-name       = "${terraform.workspace}"
    owner                  = "OPG Supervision"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = "${local.is_production}"
  }

  db_subnet_group = "rds-private-subnets-${local.vpc_name}"
  ec_subnet_group = "ec-pvt-subnets-${local.vpc_name}"

  jump_record          = "jump.${local.vpc_name}.internal"
  master_record        = "master.${local.vpc_name}.internal"
  salt_record          = "salt.${local.vpc_name}.internal"
  jump_external_record = "jump.${join(".",compact(list(local.vpc_name, local.account_name, local.domain_name )))}."
}

data "aws_ami" "opg_ubuntu_14_04" {
  name_regex  = "opg-ubuntu-14.04-docker.*"
  owners      = ["${lookup(var.account_ids, "opg-shared")}"]
  most_recent = true
}

data "aws_security_group" "shared_services" {
  name = "shared-services-${local.vpc_name}"
}

data "aws_security_group" "jumphost_client" {
  name = "jumphost-client-${local.vpc_name}"
}

data "aws_security_group" "jumphost" {
  name = "jumphost-${local.vpc_name}"
}

data "aws_security_group" "salt_master" {
  name = "salt-master-${local.vpc_name}"
}

data "aws_security_group" "maintenance" {
  count = "${local.maintenance_enabled}"
  name  = "maintenance-server-${local.vpc_name}"
}

data "aws_acm_certificate" "external" {
  domain      = "${local.external_certificate_name}"
  types       = ["AMAZON_ISSUED"]
  most_recent = true
}

data "aws_acm_certificate" "internal" {
  domain      = "${join(".",compact(list("*", local.account_name, local.domain_name )))}"
  types       = ["AMAZON_ISSUED"]
  most_recent = true
}
