module "ssm_ec2_instance_breakglass" {
  source = "./modules/ssm_ec2_instance"

  instance_type = "t3.micro"
  subnet_id     = data.aws_subnet.private[0].id
  name          = "breakglass"
  tags          = var.default_tags

  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

module "ssm_ec2_instance_operator" {
  source = "./modules/ssm_ec2_instance"

  instance_type = "t3.micro"
  subnet_id     = data.aws_subnet.private[0].id
  name          = "operator"
  tags          = var.default_tags

  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
