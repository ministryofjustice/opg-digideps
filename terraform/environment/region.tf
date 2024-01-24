module "eu_west_1" {
  source = "./region"

  # Hard coding 1 as we likely don't want to switch permanently so we leave up the infra in primary.
  # If we ever had to do a permanent switch then we would swap the count around with eu_west_2 and hard code that.
  count = 1

  account            = local.account
  default_tags       = local.default_tags
  default_role       = var.DEFAULT_ROLE
  secrets_prefix     = local.secrets_prefix
  canonical_user_ids = local.canonical_user_ids
  docker_tag         = var.OPG_DOCKER_TAG
  r53_hc_front       = aws_route53_health_check.availability_front
  r53_hc_admin       = aws_route53_health_check.availability_admin
  certificate_arn    = aws_acm_certificate_validation.wildcard.certificate_arn
  front_fqdn         = aws_route53_record.front.fqdn
  admin_fqdn         = aws_route53_record.admin.fqdn

  providers = {
    aws             = aws.digideps_eu_west_1
    aws.management  = aws.management_eu_west_1
    aws.development = aws.development
  }
}

module "eu_west_2" {
  source = "./region"

  count = local.account.secondary_region_enabled ? 1 : 0

  account            = local.account
  default_tags       = local.default_tags
  default_role       = var.DEFAULT_ROLE
  secrets_prefix     = local.secrets_prefix
  canonical_user_ids = local.canonical_user_ids
  docker_tag         = var.OPG_DOCKER_TAG
  r53_hc_front       = aws_route53_health_check.availability_front
  r53_hc_admin       = aws_route53_health_check.availability_admin
  certificate_arn    = aws_acm_certificate_validation.wildcard.certificate_arn
  front_fqdn         = aws_route53_record.front.fqdn
  admin_fqdn         = aws_route53_record.admin.fqdn

  providers = {
    aws             = aws.digideps_eu_west_2
    aws.management  = aws.management_eu_west_2
    aws.development = aws.development
  }
}
