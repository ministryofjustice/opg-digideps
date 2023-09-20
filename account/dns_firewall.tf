resource "aws_cloudwatch_log_group" "aws_route53_resolver_query_log" {
  count             = local.account.dns_firewall.enabled ? 1 : 0
  name              = "digideps-aws-route53-resolver-query-log-config"
  retention_in_days = 180
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags = {
    "Name" = "digideps-aws-route53-resolver-query-log-config"
  }
}

resource "aws_route53_resolver_query_log_config" "egress" {
  count           = local.account.dns_firewall.enabled ? 1 : 0
  name            = "egress"
  destination_arn = aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].arn
}

resource "aws_route53_resolver_query_log_config_association" "egress" {
  count                        = local.account.dns_firewall.enabled ? 1 : 0
  resolver_query_log_config_id = aws_route53_resolver_query_log_config.egress[0].id
  resource_id                  = aws_vpc.main.id
}


locals {
  service_id = [
    "logs",
    "ecr",
    "dynamodb",
    "kms",
    "secretsmanager",
    "ecr.api",
    "ssm",
  ]
}

data "aws_service" "services" {
  for_each   = toset(local.service_id)
  region     = data.aws_region.current.name
  service_id = each.value
}

locals {
  aws_service_dns_name = [for service in data.aws_service.services : "${service.dns_name}."]
  # needed for now but there are options to replace the need for these
  dns_to_replace = [
    "raw.githubusercontent.com.",
    "production.cloudflare.docker.com.",
    "auth.docker.io.",
    "registry-1.docker.io.",
  ]
  default_dns = [
    "public-keys.auth.elb.${data.aws_region.current.name}.amazonaws.com.",
    "311462405659.dkr.ecr.${data.aws_region.current.name}.amazonaws.com.",
    "prod-${data.aws_region.current.name}-starport-layer-bucket.s3.eu-west-1.amazonaws.com.",
    "api.notifications.service.gov.uk.",
    "d2kjg78kcam6ku.cloudfront.net.",
    "current.cvd.clamav.net.",
    "database.clamav.net.",
    "s3-r-w.eu-west-1.amazonaws.com.",
    "dkr.ecr.eu-west-1.amazonaws.com."
  ]
  # if we put blocks on dev env then these need to be this relaxed as only leading wildcard is valid
  development_dns = [
    "*.private.",
    "*.internal.",
    "*.complete-deputy-report.service.gov.uk.",
    "*.complete-deputy-report.service.gov.uk.",
    "*.eu-west-1.rds.amazonaws.com.",
    "*.s3.eu-west-1.amazonaws.com.",
    "*.euw1.cache.amazonaws.com.",
  ]
  account_dns = local.account.name == "development" ? concat(local.default_dns, local.development_dns) : local.default_dns
}
resource "aws_route53_resolver_firewall_domain_list" "egress_allow" {
  count = local.account.dns_firewall.enabled ? 1 : 0
  name  = "egress_allowed"
  domains = concat(
    local.account_dns,
    local.aws_service_dns_name,
    local.account.dns_firewall.domains_allowed,
    local.dns_to_replace,
  )
}

resource "aws_route53_resolver_firewall_domain_list" "egress_block" {
  count   = local.account.dns_firewall.enabled ? 1 : 0
  name    = "egress_blocked"
  domains = local.account.dns_firewall.domains_blocked
}

resource "aws_route53_resolver_firewall_rule_group" "egress" {
  count = local.account.dns_firewall.enabled ? 1 : 0
  name  = "egress"
}

resource "aws_route53_resolver_firewall_rule" "egress_allow" {
  count                   = local.account.dns_firewall.enabled ? 1 : 0
  name                    = "egress_allowed"
  action                  = "ALLOW"
  firewall_domain_list_id = aws_route53_resolver_firewall_domain_list.egress_allow[0].id
  firewall_rule_group_id  = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority                = 200
}

resource "aws_route53_resolver_firewall_rule" "egress_block" {
  count  = local.account.dns_firewall.enabled ? 1 : 0
  name   = "egress_blocked"
  action = "ALERT"
  # action                  = "BLOCK"
  # block_response          = "NODATA"
  firewall_domain_list_id = aws_route53_resolver_firewall_domain_list.egress_block[0].id
  firewall_rule_group_id  = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority                = 300
}

resource "aws_route53_resolver_firewall_rule_group_association" "egress" {
  count                  = local.account.dns_firewall.enabled ? 1 : 0
  name                   = "egress"
  firewall_rule_group_id = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority               = 500
  vpc_id                 = aws_vpc.main.id
}


resource "aws_cloudwatch_query_definition" "dns_firewall_statistics" {
  count = local.account.dns_firewall.enabled ? 1 : 0
  name  = "DNS Firewall Queries/DNS Firewall Statistics"

  log_group_names = [aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].name]

  query_string = <<EOF
fields @timestamp, query_name, firewall_rule_action
| sort @timestamp desc
| stats count() as frequency by query_name, firewall_rule_action
EOF
}
