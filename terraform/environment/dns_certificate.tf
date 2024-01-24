resource "aws_acm_certificate" "wildcard" {
  domain_name               = "*.${aws_route53_record.front.fqdn}"
  subject_alternative_names = [aws_route53_record.front.fqdn]
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
  name     = tolist(aws_acm_certificate.wildcard.domain_validation_options).0.resource_record_name
  type     = tolist(aws_acm_certificate.wildcard.domain_validation_options).0.resource_record_type
  zone_id  = data.aws_route53_zone.public.id
  records  = [tolist(aws_acm_certificate.wildcard.domain_validation_options).0.resource_record_value]
  ttl      = 60
  provider = aws.dns
}

resource "aws_acm_certificate_validation" "wildcard" {
  certificate_arn         = aws_acm_certificate.wildcard.id
  validation_record_fqdns = aws_route53_record.wildcard_validation[*].fqdn
}
