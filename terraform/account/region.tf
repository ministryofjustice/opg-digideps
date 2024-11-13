module "eu_west_1" {
  source = "./region"

  # Hard coding 1 as we likely don't want to switch permanently so we leave up the infra in primary.
  # If we ever had to do a permanent switch then we would swap the count around with eu_west_2 and hard code that.
  count = 1

  account      = local.account
  default_tags = local.default_tags

  providers = {
    aws            = aws.digideps_eu_west_1
    aws.management = aws.management_eu_west_1
    aws.global     = aws.global
    aws.eu_west_1  = aws.digideps_eu_west_1
    aws.eu_west_2  = aws.digideps_eu_west_2
  }
}

module "eu_west_2" {
  source = "./region"

  count = local.account.secondary_region_enabled ? 1 : 0

  account      = local.account
  default_tags = local.default_tags

  providers = {
    aws            = aws.digideps_eu_west_2
    aws.management = aws.management_eu_west_2
    aws.global     = aws.global
    aws.eu_west_1  = aws.digideps_eu_west_1
    aws.eu_west_2  = aws.digideps_eu_west_2
  }
}
