resource "aws_vpc_endpoint" "vpc_endpoint" {
  vpc_id              = var.vpc.id
  service_name        = "com.amazonaws.${var.region}.${var.service}"
  vpc_endpoint_type   = "Interface"
  private_dns_enabled = true
  security_group_ids  = aws_security_group.vpc_endpoint[*].id
  subnet_ids          = var.subnet_ids
  tags                = merge(var.tags, { Name = var.service_short_title })
}

#tfsec:ignore:aws-vpc-add-description-to-security-group - can't replace these, will have to be two part PR
resource "aws_security_group" "vpc_endpoint" {
  name_prefix = "${var.service_short_title}_endpoint"
  vpc_id      = var.vpc.id
  tags        = merge(var.tags, { Name = "${var.service_short_title}_endpoint" })

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "vpc_endpoint_https_in" {
  description       = "internal traffic to ${var.service_short_title} endpoint"
  from_port         = 443
  protocol          = "tcp"
  security_group_id = aws_security_group.vpc_endpoint.id
  to_port           = 443
  type              = "ingress"
  cidr_blocks       = [var.vpc.cidr_block]
}
