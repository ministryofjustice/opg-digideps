module "eu_west_1" {
  source = "./region"

  # Hard coding 1 as we likely don't want to switch permanently so we leave up the infra in primary.
  # If we ever had to do a permanent switch then we would swap the count around with eu_west_2 and hard code that.
  count = 1

  account                           = local.account
  default_tags                      = local.default_tags
  default_role                      = var.DEFAULT_ROLE
  secrets_prefix                    = local.secrets_prefix
  shared_environment_variables      = local.shared_environment_variables
  docker_tag                        = var.OPG_DOCKER_TAG
  health_check_front                = aws_route53_health_check.availability_front
  health_check_admin                = aws_route53_health_check.availability_admin
  certificate_arn                   = aws_acm_certificate_validation.wildcard.certificate_arn
  complete_deputy_report_cert_arn   = aws_acm_certificate_validation.complete_deputy_report_wildcard.certificate_arn
  front_fully_qualified_domain_name = aws_route53_record.front.fqdn
  admin_fully_qualified_domain_name = aws_route53_record.admin.fqdn

  providers = {
    aws                  = aws.digideps_eu_west_1
    aws.management       = aws.management_eu_west_1
    aws.management_admin = aws.management_admin
  }
}

module "eu_west_2" {
  source = "./region"

  count = local.account.secondary_region_enabled ? 1 : 0

  account                           = local.account
  default_tags                      = local.default_tags
  default_role                      = var.DEFAULT_ROLE
  secrets_prefix                    = local.secrets_prefix
  shared_environment_variables      = local.shared_environment_variables
  docker_tag                        = var.OPG_DOCKER_TAG
  health_check_front                = aws_route53_health_check.availability_front
  health_check_admin                = aws_route53_health_check.availability_admin
  certificate_arn                   = aws_acm_certificate_validation.wildcard.certificate_arn
  complete_deputy_report_cert_arn   = aws_acm_certificate_validation.complete_deputy_report_wildcard.certificate_arn
  front_fully_qualified_domain_name = aws_route53_record.front.fqdn
  admin_fully_qualified_domain_name = aws_route53_record.admin.fqdn

  providers = {
    aws                  = aws.digideps_eu_west_2
    aws.management       = aws.management_eu_west_2
    aws.management_admin = aws.management_eu_west_2
  }
}
