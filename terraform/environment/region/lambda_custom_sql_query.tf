data "aws_security_group" "lambda_custom_sql" {
  tags   = { Name = "${var.account.name}-custom-sql-query" }
  vpc_id = data.aws_vpc.vpc.id
}

resource "aws_security_group_rule" "lambda_custom_sql_query_to_front" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  source_security_group_id = module.api_rds_security_group.id
  security_group_id        = data.aws_security_group.lambda_custom_sql.id
  description              = "Outbound lambda custom_sql to database"
}
