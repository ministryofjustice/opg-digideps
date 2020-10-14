module "squid" {
  source     = "./squid"
  vpc_id     = data.aws_vpc.vpc.id
  aws_region = "eu-west-1"

  environment = local.environment

  lb_subnets      = data.aws_subnet.public.*.id
  fargate_subnets = data.aws_subnet.private.*.id

  desired_count = 1

  url_block_all     = false
  whitelist_url     = "api.notifications.service.gov.uk"
  default_tags      = local.default_tags
  cluster           = aws_ecs_cluster.main
  service_discovery = aws_service_discovery_private_dns_namespace.private
  extra_tags = {
    Terraform = "true"
    App       = "UTM"
  }
}
