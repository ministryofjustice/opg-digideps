locals {
  ssm_prefix = "/digideps/${terraform.workspace}"
}

data "aws_canonical_user_id" "current" {}

resource "aws_ssm_parameter" "environment_variables" {
  provider = aws.management
  name     = "${local.ssm_prefix}/environment_variables"
  type     = "String"
  value    = "{\"canonical_user_id\": \"${data.aws_canonical_user_id.current.id}\""

  tags = var.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}
