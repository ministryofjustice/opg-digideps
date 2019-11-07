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

locals {
  common_sg_rules = {
    ecr = {
      port              = 443
      security_group_id = data.aws_security_group.ecr_endpoint.id
    },
    logs = {
      port              = 443
      security_group_id = data.aws_security_group.logs_endpoint.id
    },
    s3 = {
      port           = 443
      prefix_list_id = data.aws_vpc_endpoint.s3_endpoint.prefix_list_id
    }
  }
}
