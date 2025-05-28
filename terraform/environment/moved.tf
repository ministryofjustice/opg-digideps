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

moved {
  from = aws_acm_certificate.complete_deputy_report_wildcard
  to   = aws_acm_certificate.complete_deputy_report_wildcard[0]
}
moved {
  from = aws_acm_certificate_validation.complete_deputy_report_wildcard
  to   = aws_acm_certificate_validation.complete_deputy_report_wildcard[0]
}
moved {
  from = aws_route53_record.complete_deputy_report_admin
  to   = aws_route53_record.complete_deputy_report_admin[0]
}
moved {
  from = aws_route53_record.complete_deputy_report_front
  to   = aws_route53_record.complete_deputy_report_front[0]
}
moved {
  from = aws_route53_record.complete_deputy_report_wildcard_validation
  to   = aws_route53_record.complete_deputy_report_wildcard_validation[0]
}
moved {
  from = aws_route53_record.complete_deputy_report_www
  to   = aws_route53_record.complete_deputy_report_www[0]
}
