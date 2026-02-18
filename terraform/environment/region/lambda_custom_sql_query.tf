data "aws_security_group" "lambda_custom_sql_tool" {
  name   = "${var.account.name}-custom-sql-tool"
  vpc_id = data.aws_vpc.main.id
}

resource "aws_security_group_rule" "lambda_custom_sql_query_to_front" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  source_security_group_id = module.api_rds_security_group.id
  security_group_id        = data.aws_security_group.lambda_custom_sql_tool.id
  description              = "Outbound lambda custom_sql to database"
}
