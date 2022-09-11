resource "aws_vpc_endpoint" "ecr" {
  vpc_id              = aws_vpc.main.id
  service_name        = "com.amazonaws.eu-west-1.ecr.dkr"
  vpc_endpoint_type   = "Interface"
  private_dns_enabled = true
  security_group_ids  = aws_security_group.ecr_endpoint[*].id
  subnet_ids          = aws_subnet.private[*].id
  tags                = merge(local.default_tags, { Name = "ecr" })
}

#tfsec:ignore:aws-vpc-add-description-to-security-group - can't replace these, will have to be two part PR
resource "aws_security_group" "ecr_endpoint" {
  name_prefix = "ecr_endpoint"
  vpc_id      = aws_vpc.main.id
  tags        = merge(local.default_tags, { Name = "ecr_endpoint" })

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "ecr_endpoint_https_in" {
  description       = "internal traffic to ecr endpoint"
  from_port         = 443
  protocol          = "tcp"
  security_group_id = aws_security_group.ecr_endpoint.id
  to_port           = 443
  type              = "ingress"
  cidr_blocks       = [aws_vpc.main.cidr_block]
}



resource "aws_vpc_endpoint" "ecr_api" {
  vpc_id              = aws_vpc.main.id
  service_name        = "com.amazonaws.eu-west-1.ecr.api"
  vpc_endpoint_type   = "Interface"
  private_dns_enabled = true
  security_group_ids  = aws_security_group.ecr_api_endpoint[*].id
  subnet_ids          = aws_subnet.private[*].id
  tags                = merge(local.default_tags, { Name = "ecr_api" })
}

#tfsec:ignore:aws-vpc-add-description-to-security-group - can't replace these, will have to be two part PR
resource "aws_security_group" "ecr_api_endpoint" {
  name_prefix = "ecr_api_endpoint"
  vpc_id      = aws_vpc.main.id
  tags        = merge(local.default_tags, { Name = "ecr_api_endpoint" })

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "ecr_api_endpoint_https_in" {
  description       = "internal traffic to api ecr endpoint"
  from_port         = 443
  protocol          = "tcp"
  security_group_id = aws_security_group.ecr_api_endpoint.id
  to_port           = 443
  type              = "ingress"
  cidr_blocks       = [aws_vpc.main.cidr_block]
}
