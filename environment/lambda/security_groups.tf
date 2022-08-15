resource "aws_security_group" "lambda" {
  name_prefix = "${var.lambda_name}.${var.environment}"
  vpc_id      = var.vpc_id
  description = var.description

  lifecycle {
    create_before_destroy = true
  }

  revoke_rules_on_delete = true

  tags = merge(
    var.tags,
    {
      "Name" = var.lambda_name
    },
  )
}
