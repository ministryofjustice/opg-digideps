moved {
  from = aws_acm_certificate.wildcard
  to   = aws_acm_certificate.wildcard[0]
}
moved {
  from = aws_acm_certificate_validation.wildcard
  to   = aws_acm_certificate_validation.wildcard[0]
}
moved {
  from = aws_route53_record.admin
  to   = aws_route53_record.admin[0]
}
moved {
  from = aws_route53_record.front
  to   = aws_route53_record.front[0]
}
moved {
  from = aws_route53_record.wildcard_validation
  to   = aws_route53_record.wildcard_validation[0]
}
moved {
  from = aws_route53_record.www
  to   = aws_route53_record.www[0]
}
