resource "aws_vpc_endpoint" "secrets" {
  vpc_id              = aws_vpc.main.id
  service_name        = "com.amazonaws.eu-west-1.secretsmanager"
  vpc_endpoint_type   = "Interface"
  private_dns_enabled = true
  security_group_ids  = aws_security_group.secrets_endpoint[*].id
  subnet_ids          = aws_subnet.private[*].id
  tags                = merge(local.default_tags, { Name = "secrets" })
}

#tfsec:ignore:aws-vpc-add-description-to-security-group - can't replace these, will have to be two part PR
resource "aws_security_group" "secrets_endpoint" {
  name_prefix = "secrets_endpoint"
  vpc_id      = aws_vpc.main.id
  tags        = merge(local.default_tags, { Name = "secrets_endpoint" })

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "secrets_endpoint_https_in" {
  description       = "internal traffic to secrets endpoint"
  from_port         = 443
  protocol          = "tcp"
  security_group_id = aws_security_group.secrets_endpoint.id
  to_port           = 443
  type              = "ingress"
  cidr_blocks       = [aws_vpc.main.cidr_block]
}
