# VPC Endpoints
module "secrets_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "secretsmanager"
  service_short_title = "secrets"
  tags                = var.default_tags
}

module "logs_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "logs"
  service_short_title = "logs"
  tags                = var.default_tags
}

module "ssm_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ssm"
  service_short_title = "ssm"
  tags                = var.default_tags
}

module "ec2messages_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ec2messages"
  service_short_title = "ec2messages"
  tags                = var.default_tags
}

module "ssmmessages_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ssmmessages"
  service_short_title = "ssmmessages"
  tags                = var.default_tags
}

module "sts_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "sts"
  service_short_title = "sts"
  tags                = var.default_tags
}

module "rds_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "rds"
  service_short_title = "rds"
  tags                = var.default_tags
}
