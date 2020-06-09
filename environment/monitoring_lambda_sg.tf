data "aws_vpc_endpoint" "secrets_endpoint" {
  service_name = "com.amazonaws.eu-west-1.secretsmanager"
  vpc_id       = data.aws_vpc.vpc.id
}

locals {
  monitoring_lambda_sg_rules = {
    rds = {
      port        = 5432
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
    secrets = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_vpc_endpoint.secrets_endpoint.id
    }
  }
}

module "monitoring_lambda_security_group" {
  source = "./security_group"
  rules  = local.monitoring_lambda_sg_rules
  name   = "monitoring-lambda"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
