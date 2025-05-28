locals {
  complete_deputy_report = "complete-deputy-report.service.gov.uk"
  service_justice_domain = "digideps.opg.service.justice.gov.uk"
  front_loadbalancer     = local.primary_region_name == "eu-west-1" ? module.eu_west_1[0].aws_lb_front : module.eu_west_2[0].aws_lb_front
  admin_loadbalancer     = local.primary_region_name == "eu-west-1" ? module.eu_west_1[0].aws_lb_admin : module.eu_west_2[0].aws_lb_admin
}

# Main complete-deputy-report DNS in Digideps Production Account
data "aws_route53_zone" "public" {
  count    = local.old_prod_dns
  name     = local.complete_deputy_report
  provider = aws.dns
}

resource "aws_route53_record" "front" {
  count   = local.old_prod_dns
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.public[0].id

  alias {
    evaluate_target_health = false
    name                   = local.front_loadbalancer.dns_name
    zone_id                = local.front_loadbalancer.zone_id
  }
  provider = aws.dns
}

resource "aws_route53_record" "admin" {
  count   = local.old_prod_dns
  name    = join(".", compact(["admin", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.public[0].id

  alias {
    evaluate_target_health = false
    name                   = local.admin_loadbalancer.dns_name
    zone_id                = local.admin_loadbalancer.zone_id
  }
  provider = aws.dns
}

resource "aws_route53_record" "www" {
  count   = local.old_prod_dns
  name    = join(".", compact(["www", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.public[0].id

  alias {
    evaluate_target_health = false
    name                   = local.front_loadbalancer.dns_name
    zone_id                = local.front_loadbalancer.zone_id
  }
  provider = aws.dns
}

# New complete-deputy-report DNS in Management Account
data "aws_route53_zone" "complete_deputy_report" {
  name     = local.complete_deputy_report
  provider = aws.management_eu_west_1
}

resource "aws_route53_record" "complete_deputy_report_front" {
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.complete_deputy_report.id

  alias {
    evaluate_target_health = false
    name                   = local.front_loadbalancer.dns_name
    zone_id                = local.front_loadbalancer.zone_id
  }
  provider = aws.management_eu_west_1
}

resource "aws_route53_record" "complete_deputy_report_admin" {
  name    = join(".", compact(["admin", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.complete_deputy_report.id

  alias {
    evaluate_target_health = false
    name                   = local.admin_loadbalancer.dns_name
    zone_id                = local.admin_loadbalancer.zone_id
  }
  provider = aws.management_eu_west_1
}

resource "aws_route53_record" "complete_deputy_report_www" {
  name    = join(".", compact(["www", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.complete_deputy_report.id

  alias {
    evaluate_target_health = false
    name                   = local.front_loadbalancer.dns_name
    zone_id                = local.front_loadbalancer.zone_id
  }
  provider = aws.management_eu_west_1
}

# Alternative Service Justice Records

data "aws_route53_zone" "service" {
  name     = local.service_justice_domain
  provider = aws.management_eu_west_1
}

resource "aws_route53_record" "service_front" {
  count   = local.account.is_production == 1 ? 0 : 1
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.service.id

  alias {
    evaluate_target_health = false
    name                   = local.front_loadbalancer.dns_name
    zone_id                = local.front_loadbalancer.zone_id
  }
  provider = aws.management_eu_west_1
}

resource "aws_route53_record" "service_admin" {
  name    = join(".", compact([local.subdomain, "admin"]))
  type    = "A"
  zone_id = data.aws_route53_zone.service.id

  alias {
    evaluate_target_health = false
    name                   = local.admin_loadbalancer.dns_name
    zone_id                = local.admin_loadbalancer.zone_id
  }
  provider = aws.management_eu_west_1
}
