resource "aws_security_group_rule" "api_rds_cloud9_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 5432
  to_port           = 5432
  security_group_id = aws_security_group.api_rds.id
  cidr_blocks       = [data.aws_vpc.vpc.cidr_block]
}

