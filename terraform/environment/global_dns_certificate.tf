resource "aws_acm_certificate" "wildcard" {
  count                     = local.old_prod_dns
  domain_name               = "*.${aws_route53_record.front[0].fqdn}"
  subject_alternative_names = [aws_route53_record.front[0].fqdn]
  validation_method         = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(
    local.default_tags,
    {
      "Name" = "wildcard-certificate-${local.environment}"
    },
  )
}

resource "aws_route53_record" "wildcard_validation" {
  count    = local.old_prod_dns
  name     = tolist(aws_acm_certificate.wildcard[0].domain_validation_options)[0].resource_record_name
  type     = tolist(aws_acm_certificate.wildcard[0].domain_validation_options)[0].resource_record_type
  zone_id  = data.aws_route53_zone.public[0].id
  records  = [tolist(aws_acm_certificate.wildcard[0].domain_validation_options)[0].resource_record_value]
  ttl      = 60
  provider = aws.dns
}

resource "aws_acm_certificate_validation" "wildcard" {
  count                   = local.old_prod_dns
  certificate_arn         = aws_acm_certificate.wildcard[0].id
  validation_record_fqdns = aws_route53_record.wildcard_validation[0][*].fqdn
}

# New wildcard certs. For the same domain but different account
resource "aws_acm_certificate" "complete_deputy_report_wildcard" {
  count                     = local.old_prod_dns
  domain_name               = "*.${aws_route53_record.complete_deputy_report_front.fqdn}"
  subject_alternative_names = [aws_route53_record.complete_deputy_report_front.fqdn]
  validation_method         = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(
    local.default_tags,
    {
      "Name" = "cdr-wildcard-certificate-${local.environment}"
    },
  )
}

resource "aws_route53_record" "complete_deputy_report_wildcard_validation" {
  count    = local.old_prod_dns
  name     = tolist(aws_acm_certificate.complete_deputy_report_wildcard[0].domain_validation_options)[0].resource_record_name
  type     = tolist(aws_acm_certificate.complete_deputy_report_wildcard[0].domain_validation_options)[0].resource_record_type
  zone_id  = data.aws_route53_zone.complete_deputy_report.id
  records  = [tolist(aws_acm_certificate.complete_deputy_report_wildcard[0].domain_validation_options)[0].resource_record_value]
  ttl      = 60
  provider = aws.management_eu_west_1
}

resource "aws_acm_certificate_validation" "complete_deputy_report_wildcard" {
  count                   = local.old_prod_dns
  certificate_arn         = aws_acm_certificate.complete_deputy_report_wildcard[0].id
  validation_record_fqdns = aws_route53_record.complete_deputy_report_wildcard_validation[0][*].fqdn
}
