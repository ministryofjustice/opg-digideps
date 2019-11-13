locals {
  cloud9_sg_rules = {
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "ingress"
      target_type = "cidr_block"
      target      = data.aws_vpc.vpc.cidr_block
    }
  }
}

module "cloud9_security_group" {
  source = "./security_group"
  rules  = local.cloud9_sg_rules
  name   = "cloud9"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
