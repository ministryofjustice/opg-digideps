resource "aws_cloudwatch_log_group" "aws_route53_resolver_query_log" {
  count             = var.account.dns_firewall.enabled ? 1 : 0
  name              = "digideps-aws-route53-resolver-query-log-config"
  retention_in_days = 180
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  tags = {
    "Name" = "digideps-aws-route53-resolver-query-log-config"
  }
}

resource "aws_route53_resolver_query_log_config" "egress" {
  count           = var.account.dns_firewall.enabled ? 1 : 0
  name            = "egress"
  destination_arn = aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].arn
}

resource "aws_route53_resolver_query_log_config_association" "egress" {
  count                        = var.account.dns_firewall.enabled ? 1 : 0
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

  default_dns = [
    "raw.githubusercontent.com.",
    "production.cloudflare.docker.com.",
    "auth.docker.io.",
    "registry-1.docker.io.",
    "sts.amazonaws.com.",
    "www.gov.uk.",
    "www.ncsc.gov.uk.",
    "repo.${data.aws_region.current.name}.amazonaws.com.",
    "rpm.releases.hashicorp.com.",
    "public-keys.auth.elb.${data.aws_region.current.name}.amazonaws.com.",
    "311462405659.dkr.ecr.${data.aws_region.current.name}.amazonaws.com.",
    "prod-${data.aws_region.current.name}-starport-layer-bucket.s3.${data.aws_region.current.name}.amazonaws.com.",
    "api.notifications.service.gov.uk.",
    "d2kjg78kcam6ku.cloudfront.net.",
    "current.cvd.clamav.net.",
    "database.clamav.net.",
    "database.clamav.net.cdn.cloudflare.net.",
    "api.ecr.${data.aws_region.current.name}.amazonaws.com.",
    "packages.${data.aws_region.current.name}.amazonaws.com.",
    "dkr.ecr.${data.aws_region.current.name}.amazonaws.com.",
    "rds.${data.aws_region.current.name}.amazonaws.com.",
    "logs.${data.aws_region.current.name}.amazonaws.com.",
    "secretsmanager.${data.aws_region.current.name}.amazonaws.com.",
    "ssm.${data.aws_region.current.name}.amazonaws.com.",
    "s3-r-w.${data.aws_region.current.name}.amazonaws.com.",
    "s3.${data.aws_region.current.name}.amazonaws.com.",
    "s3-3-w.amazonaws.com.",
    "s3-r-w.dualstack.${data.aws_region.current.name}.amazonaws.com.",
  ]
  # if we put blocks on dev env then these need to be this relaxed as only leading wildcard is valid
  development_dns = [
    "*.private.",
    "*.internal.",
    "*.complete-deputy-report.service.gov.uk.",
    "*.${data.aws_region.current.name}.rds.amazonaws.com.",
    "*.s3.${data.aws_region.current.name}.amazonaws.com.",
    "*.euw1.cache.amazonaws.com.",

  ]
  production_dns_alert = [
    "wildcard.fedoraproject.org.",
    "yum.corretto.aws.",
    "*.amazon.pool.ntp.org.",
    "*.205.245.34.in-addr.arpa.",
    "api.github.com.",
    "client-telemetry.us-east-1.amazonaws.com.",
    "cognito-identity.us-east-1.amazonaws.com.",
    "*.cloudfront.net.",
    "*.${data.aws_region.current.name}.compute.amazonaws.com.",
    "ec2-instance-connect.${data.aws_region.current.name}.amazonaws.com.",
    "*.dd.opg.digital.",
    "*.prod-vpc.internal.",
    "*.pythonhosted.org.",
    "*.prod-vpc.internaldd.opg.digital.prod-vpc.internaldd.opg.digital.",
    "ip-10-172-90-58.",
    "instance-data.",
    "idetoolkits.amazonwebservices.com.",
  ]
  production_dns_allow = [
    "complete-deputy-report.service.gov.uk.",
    "deputy-reporting.api.opg.service.justice.gov.uk.",
    "amazonlinux-2-repos-${data.aws_region.current.name}.s3.dualstack.${data.aws_region.current.name}.amazonaws.com.",
    "pa-uploads-production02.s3.${data.aws_region.current.name}.amazonaws.com.",
    "api-production02-0.c15chafpzt4q.${data.aws_region.current.name}.rds.amazonaws.com.",
    "postgres.preproduction.internal.",
    "postgres.preproduction.internal.dd.opg.digital.",
    "postgres.production02.internal.",
    "frontend-redis.production02.internal.",
    "api-redis.production02.internal.",
    "front.production02.private.",
    "api.production02.private.",
    "htmltopdf.production02.private.",
    "scan.production02.private.",
    "secretsmanager.${data.aws_region.current.name}.amazonaws.com."
  ]

  production_dns_combined    = concat(local.default_dns, local.production_dns_alert, local.production_dns_allow)
  preproduction_dns_combined = local.default_dns
  development_dns_combined   = concat(local.default_dns, local.development_dns)

  combined_dns = {
    production    = local.production_dns_combined,
    preproduction = local.preproduction_dns_combined
    development   = local.development_dns_combined
  }
}
resource "aws_route53_resolver_firewall_domain_list" "egress_allow" {
  count = var.account.dns_firewall.enabled ? 1 : 0
  name  = "egress_allowed"
  domains = concat(
    local.combined_dns[var.account.name],
    local.aws_service_dns_name,
    var.account.dns_firewall.domains_allowed,
  )
}

resource "aws_route53_resolver_firewall_domain_list" "egress_block" {
  count   = var.account.dns_firewall.enabled ? 1 : 0
  name    = "egress_blocked"
  domains = var.account.dns_firewall.domains_blocked
}

resource "aws_route53_resolver_firewall_rule_group" "egress" {
  count = var.account.dns_firewall.enabled ? 1 : 0
  name  = "egress"
}

resource "aws_route53_resolver_firewall_rule" "egress_allow" {
  count                   = var.account.dns_firewall.enabled ? 1 : 0
  name                    = "egress_allowed"
  action                  = "ALLOW"
  firewall_domain_list_id = aws_route53_resolver_firewall_domain_list.egress_allow[0].id
  firewall_rule_group_id  = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority                = 200
}

resource "aws_route53_resolver_firewall_rule" "egress_block" {
  count  = var.account.dns_firewall.enabled ? 1 : 0
  name   = "egress_blocked"
  action = "ALERT"
  # action                  = "BLOCK"
  # block_response          = "NXDOMAIN"
  firewall_domain_list_id = aws_route53_resolver_firewall_domain_list.egress_block[0].id
  firewall_rule_group_id  = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority                = 300
}

resource "aws_route53_resolver_firewall_rule_group_association" "egress" {
  count                  = var.account.dns_firewall.enabled ? 1 : 0
  name                   = "egress"
  firewall_rule_group_id = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority               = 500
  vpc_id                 = aws_vpc.main.id
}


resource "aws_cloudwatch_query_definition" "dns_firewall_statistics" {
  count = var.account.dns_firewall.enabled ? 1 : 0
  name  = "DNS Firewall Queries/DNS Firewall Statistics"

  log_group_names = [aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].name]

  query_string = <<EOF
fields @timestamp, query_name, firewall_rule_action
| sort @timestamp desc
| stats count() as frequency by query_name, firewall_rule_action
EOF
}
