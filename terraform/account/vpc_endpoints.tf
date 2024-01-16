module "secrets_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "secretsmanager"
  service_short_title = "secrets"
  tags                = local.default_tags
}

module "ecr_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "ecr.dkr"
  service_short_title = "ecr"
  tags                = local.default_tags
}

module "ecr_api_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "ecr.api"
  service_short_title = "ecr_api"
  tags                = local.default_tags
}

module "logs_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "logs"
  service_short_title = "logs"
  tags                = local.default_tags
}

module "ssm_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "ssm"
  service_short_title = "ssm"
  tags                = local.default_tags
}

resource "aws_vpc_endpoint" "s3" {
  service_name      = "com.amazonaws.eu-west-1.s3"
  vpc_id            = aws_vpc.main.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = aws_route_table.private[*].id
  tags              = merge(local.default_tags, { Name = "s3" })
}

# Moved data (to remove)
moved {
  from = aws_vpc_endpoint.secrets
  to   = module.secrets_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}

moved {
  from = aws_security_group.secrets_endpoint
  to   = module.secrets_vpc_endpoint.aws_security_group.vpc_endpoint
}

moved {
  from = aws_security_group_rule.secrets_endpoint_https_in
  to   = module.secrets_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}

moved {
  from = aws_vpc_endpoint.ssm
  to   = module.ssm_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}

moved {
  from = aws_security_group.ssm_endpoint
  to   = module.ssm_vpc_endpoint.aws_security_group.vpc_endpoint
}

moved {
  from = aws_security_group_rule.ssm_endpoint_https_in
  to   = module.ssm_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}

moved {
  from = aws_vpc_endpoint.logs
  to   = module.logs_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}

moved {
  from = aws_security_group.logs_endpoint
  to   = module.logs_vpc_endpoint.aws_security_group.vpc_endpoint
}

moved {
  from = aws_security_group_rule.logs_endpoint_https_in
  to   = module.logs_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}

moved {
  from = aws_vpc_endpoint.ecr
  to   = module.ecr_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}

moved {
  from = aws_security_group.ecr_endpoint
  to   = module.ecr_vpc_endpoint.aws_security_group.vpc_endpoint
}

moved {
  from = aws_security_group_rule.ecr_endpoint_https_in
  to   = module.ecr_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}

moved {
  from = aws_vpc_endpoint.ecr_api
  to   = module.ecr_api_vpc_endpoint.aws_vpc_endpoint.vpc_endpoint
}

moved {
  from = aws_security_group.ecr_api_endpoint
  to   = module.ecr_api_vpc_endpoint.aws_security_group.vpc_endpoint
}

moved {
  from = aws_security_group_rule.ecr_api_endpoint_https_in
  to   = module.ecr_api_vpc_endpoint.aws_security_group_rule.vpc_endpoint_https_in
}
