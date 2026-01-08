# Security Groups

resource "aws_security_group" "custom_sql_query_sg" {
  name        = "${var.account.name}-${local.lambda_custom_sql_name}"
  vpc_id      = module.network[0].vpc.id
  description = "Custom SQL Shared Lambda"

  lifecycle {
    create_before_destroy = true
  }

  revoke_rules_on_delete = true

  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-custom-sql-query" },
  )
}
