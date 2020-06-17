locals {
  mock_sirius_integration_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    front = {
      port        = 443
      protocol    = "tcp"
      type        = "egress"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "mock_sirius_integration_security_group" {
  source = "./security_group"
  rules  = local.mock_sirius_integration_sg_rules
  name   = "mock-sirius-integration"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
