locals {
  domain = "complete-deputy-report.service.gov.uk"
}
data "aws_route53_zone" "public" {
  name     = local.domain
  provider = "aws.dns"
}

resource "aws_route53_record" "front" {
  name    = local.subdomain
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = aws_lb.front.dns_name
    zone_id                = aws_lb.front.zone_id
  }
  provider = "aws.dns"
}

resource "aws_route53_record" "admin" {
  name    = join(".", compact(["admin", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = aws_lb.admin.dns_name
    zone_id                = aws_lb.admin.zone_id
  }
  provider = "aws.dns"
}

resource "aws_route53_record" "www" {
  name    = join(".", compact(["www", local.subdomain]))
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = aws_lb.front.dns_name
    zone_id                = aws_lb.front.zone_id
  }
  provider = "aws.dns"
}
