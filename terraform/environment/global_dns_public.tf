locals {
  complete_deputy_report = "complete-deputy-report.service.gov.uk"
  service_justice_domain = "digideps.opg.service.justice.gov.uk"
}

# Main complete-deputy-report DNS in Digideps Production Account
data "aws_route53_zone" "public" {
  name     = local.complete_deputy_report
  provider = aws.dns
}

resource "aws_route53_record" "front" {
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_front.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_front.zone_id
  }
  provider = aws.dns
}

resource "aws_route53_record" "admin" {
  name    = join(".", compact(["admin", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_admin.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_admin.zone_id
  }
  provider = aws.dns
}

resource "aws_route53_record" "www" {
  name    = join(".", compact(["www", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_front.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_front.zone_id
  }
  provider = aws.dns
}

# New complete-deputy-report DNS in Management Account
data "aws_route53_zone" "complete_deputy_report" {
  name     = local.complete_deputy_report
  provider = aws.management
}

resource "aws_route53_record" "complete_deputy_report_front" {
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.complete_deputy_report.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_front.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_front.zone_id
  }
  provider = aws.management
}

resource "aws_route53_record" "complete_deputy_report_admin" {
  name    = join(".", compact(["admin", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.complete_deputy_report.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_admin.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_admin.zone_id
  }
  provider = aws.management
}

resource "aws_route53_record" "complete_deputy_report_www" {
  name    = join(".", compact(["www", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.complete_deputy_report.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_front.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_front.zone_id
  }
  provider = aws.management
}

# Alternative Service Justice Records

data "aws_route53_zone" "service" {
  name     = local.service_justice_domain
  provider = aws.management
}

resource "aws_route53_record" "service_front" {
  count   = local.account.is_production == 1 ? 0 : 1
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.service.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_front.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_front.zone_id
  }
  provider = aws.management
}

resource "aws_route53_record" "service_admin" {
  name    = join(".", compact([local.subdomain, "admin"]))
  type    = "A"
  zone_id = data.aws_route53_zone.service.id

  alias {
    evaluate_target_health = false
    name                   = module.eu_west_1[0].aws_lb_admin.dns_name
    zone_id                = module.eu_west_1[0].aws_lb_admin.zone_id
  }
  provider = aws.management
}