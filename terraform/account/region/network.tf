locals {
  firewall_config = lookup(local.account_level_configurations, terraform.workspace, local.account_level_configurations["production"])
  account_level_configurations = {
    development = {
      network_firewall_enabled      = true
      none_matching_traffic_action  = "alert"
      shared_firewall_configuration = null
      # shared_firewall_configuration = {
      #   account_id   = "679638075911"
      #   account_name = "development"
      # }
    }
    preproduction = {
      network_firewall_enabled      = false
      none_matching_traffic_action  = "alert"
      shared_firewall_configuration = null
      # shared_firewall_configuration = {
      #   account_id   = "997462338508"
      #   account_name = "production"
      # }
    }
    production = {
      network_firewall_enabled      = false
      none_matching_traffic_action  = "alert"
      shared_firewall_configuration = null
    }
  }
  allowed_domains = [
    "www.gov.uk"
  ]
  allowed_prefixed_domains = [
    ".api.opg.service.justice.gov.uk"
  ]
}

module "network" {
  count                                                   = var.account.network.enabled ? 1 : 0
  source                                                  = "git@github.com:ministryofjustice/opg-terraform-aws-firewalled-network.git?ref=v1.1.0"
  cidr                                                    = data.aws_region.current.name == "eu-west-1" ? var.account.network.cidr_eu_west_1 : var.account.network.cidr_eu_west_2
  default_security_group_ingress                          = []
  default_security_group_egress                           = []
  dhcp_options_domain_name                                = "${var.account.name}.internal"
  enable_dns_hostnames                                    = true
  flow_log_cloudwatch_log_group_kms_key_id                = module.logs_kms.eu_west_1_target_key_arn
  flow_log_cloudwatch_log_group_retention_in_days         = 400
  network_firewall_enabled                                = local.firewall_config.network_firewall_enabled
  shared_firewall_configuration                           = local.firewall_config.shared_firewall_configuration
  network_firewall_cloudwatch_log_group_kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  network_firewall_cloudwatch_log_group_retention_in_days = 400
  aws_networkfirewall_firewall_policy                     = local.firewall_config.shared_firewall_configuration != null ? null : aws_networkfirewall_firewall_policy.main[0]
}

resource "aws_networkfirewall_firewall_policy" "main" {
  count = var.account.network.enabled ? 1 : 0
  name  = "main"

  firewall_policy {
    stateless_default_actions          = ["aws:forward_to_sfe"]
    stateless_fragment_default_actions = ["aws:forward_to_sfe"]

    stateful_engine_options {
      rule_order              = "DEFAULT_ACTION_ORDER"
      stream_exception_policy = "DROP"
    }
    stateful_rule_group_reference {
      resource_arn = aws_networkfirewall_rule_group.rule_file[0].arn
    }
  }
}

resource "aws_networkfirewall_rule_group" "rule_file" {
  count    = var.account.network.enabled ? 1 : 0
  capacity = 100
  name     = "main-${replace(filebase64sha256("${path.module}/network_firewall_rules.rules.tpl"), "/[^[:alnum:]]/", "")}"
  type     = "STATEFUL"
  rules = templatefile("${path.module}/network_firewall_rules.rules.tpl", {
    action                   = local.firewall_config.none_matching_traffic_action
    allowed_domains          = local.allowed_domains
    allowed_prefixed_domains = local.allowed_prefixed_domains
    }
  )
  lifecycle {
    create_before_destroy = true
  }
}
