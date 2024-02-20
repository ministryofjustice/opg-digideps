module "secrets_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "secretsmanager"
  service_short_title = "secrets"
  tags                = var.default_tags
}

module "ecr_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "ecr.dkr"
  service_short_title = "ecr"
  tags                = var.default_tags
}

module "ecr_api_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "ecr.api"
  service_short_title = "ecr_api"
  tags                = var.default_tags
}

module "logs_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "logs"
  service_short_title = "logs"
  tags                = var.default_tags
}

module "ssm_vpc_endpoint" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = aws_subnet.private[*].id
  vpc                 = aws_vpc.main
  region              = data.aws_region.current.name
  service             = "ssm"
  service_short_title = "ssm"
  tags                = var.default_tags
}

resource "aws_vpc_endpoint" "s3" {
  service_name      = "com.amazonaws.eu-west-1.s3"
  vpc_id            = aws_vpc.main.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = aws_route_table.private[*].id
  tags              = merge(var.default_tags, { Name = "s3" })
}
