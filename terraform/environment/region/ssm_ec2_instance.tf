module "ssm_ec2_instance" {
  source = "./modules/ssm_ec2_instance"

  instance_type      = "t3.micro"
  subnet_id          = data.aws_subnet.private[0].id
  security_group_ids = [module.api_rds_security_group.id]
  name               = "ssm-instance"
  tags               = var.default_tags

  vpc_id             = data.aws_vpc.vpc.id
  private_subnet_ids = [for s in data.aws_subnet.private : s.id]
  endpoint_sg_id     = module.api_rds_security_group.id
}
