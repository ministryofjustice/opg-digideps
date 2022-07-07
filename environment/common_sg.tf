data "aws_security_group" "ecr_endpoint" {
  tags   = { Name = "ecr_endpoint" }
  vpc_id = data.aws_vpc.vpc.id
}

data "aws_security_group" "logs_endpoint" {
  tags   = { Name = "logs_endpoint" }
  vpc_id = data.aws_vpc.vpc.id
}

data "aws_vpc_endpoint" "s3_endpoint" {
  service_name = "com.amazonaws.eu-west-1.s3"
  vpc_id       = data.aws_vpc.vpc.id
}

data "aws_security_group" "ssm_endpoint" {
  tags   = { Name = "ssm_endpoint" }
  vpc_id = data.aws_vpc.vpc.id
}

data "aws_security_group" "secrets_endpoint" {
  tags   = { Name = "secrets_endpoint" }
  vpc_id = data.aws_vpc.vpc.id
}

data "aws_security_group" "ecr_api_endpoint" {
  tags   = { Name = "ecr_api_endpoint" }
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  common_sg_rules = {
    ecr = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.ecr_endpoint.id
    },
    logs = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.logs_endpoint.id
    },
    s3 = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "prefix_list_id"
      target      = data.aws_vpc_endpoint.s3_endpoint.prefix_list_id
    },
    ssm = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.ssm_endpoint.id
    }
    secrets = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.secrets_endpoint.id
    }
    ecr_api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.ecr_api_endpoint.id
    }
    api_service = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
  }
}
