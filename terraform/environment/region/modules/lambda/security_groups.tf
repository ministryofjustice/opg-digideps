resource "aws_security_group" "lambda" {
  name        = "${var.environment}-${var.lambda_name}"
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
