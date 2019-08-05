data "aws_route53_zone" "domain" {
  name = local.account.domain
}

resource "aws_acm_certificate" "wildcard" {
  domain_name       = "*.${data.aws_route53_zone.domain.name}"
  validation_method = "DNS"

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_route53_record" "wildcard" {
  name    = aws_acm_certificate.wildcard.domain_validation_options[0].resource_record_name
  type    = aws_acm_certificate.wildcard.domain_validation_options[0].resource_record_type
  zone_id = data.aws_route53_zone.domain.id
  records = [aws_acm_certificate.wildcard.domain_validation_options[0].resource_record_value]
  ttl     = 60
}

resource "aws_acm_certificate_validation" "wildcard" {
  certificate_arn         = aws_acm_certificate.wildcard.arn
  validation_record_fqdns = [aws_route53_record.wildcard.fqdn]
}
