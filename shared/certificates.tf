data "aws_route53_zone" "digideps_service_justice" {
  name     = "digideps.opg.service.justice.gov.uk"
  provider = aws.management
}

#Base URLs
resource "aws_route53_record" "certificate_validation_app" {
  provider = aws.management
  for_each = {
    for dvo in aws_acm_certificate.digideps_service_justice.domain_validation_options : dvo.domain_name => {
      name   = dvo.resource_record_name
      record = dvo.resource_record_value
      type   = dvo.resource_record_type
    }
  }

  allow_overwrite = true
  name            = each.value.name
  records         = [each.value.record]
  ttl             = 60
  type            = each.value.type
  zone_id         = data.aws_route53_zone.digideps_service_justice.zone_id
}

resource "aws_acm_certificate" "digideps_service_justice" {
  domain_name       = "*.digideps.opg.service.justice.gov.uk"
  validation_method = "DNS"
}

resource "aws_acm_certificate_validation" "digideps_service_justice" {
  certificate_arn         = aws_acm_certificate.digideps_service_justice.arn
  validation_record_fqdns = [for record in aws_route53_record.certificate_validation_app : record.fqdn]
}

#Admin URLs
resource "aws_route53_record" "certificate_validation_admin" {
  provider = aws.management
  for_each = {
    for dvo in aws_acm_certificate.digideps_service_justice_admin.domain_validation_options : dvo.domain_name => {
      name   = dvo.resource_record_name
      record = dvo.resource_record_value
      type   = dvo.resource_record_type
    }
  }

  allow_overwrite = true
  name            = each.value.name
  records         = [each.value.record]
  ttl             = 60
  type            = each.value.type
  zone_id         = data.aws_route53_zone.digideps_service_justice.zone_id
}

resource "aws_acm_certificate" "digideps_service_justice_admin" {
  domain_name       = "*.admin.digideps.opg.service.justice.gov.uk"
  validation_method = "DNS"
}

resource "aws_acm_certificate_validation" "digideps_service_justice_admin" {
  certificate_arn         = aws_acm_certificate.digideps_service_justice_admin.arn
  validation_record_fqdns = [for record in aws_route53_record.certificate_validation_admin : record.fqdn]
}
