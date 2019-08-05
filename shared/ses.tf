resource "aws_ses_domain_identity" "domain" {
  domain = data.aws_route53_zone.domain.name
}

resource "aws_route53_record" "domain_ses_verification_record" {
  zone_id = data.aws_route53_zone.domain.zone_id
  name    = "_amazonses"
  type    = "TXT"
  ttl     = "300"
  records = [aws_ses_domain_identity.domain.verification_token]
}
