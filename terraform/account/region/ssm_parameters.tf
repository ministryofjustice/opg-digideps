locals {
  ssm_prefix         = "/digideps/${terraform.workspace}"
  replication_bucket = terraform.workspace == "development" ? aws_s3_bucket.pa_uploads_branch_replication[0].arn : ""
  ev_value_dev       = "{\"canonical_user_id\":\"${data.aws_canonical_user_id.current.id}\",\"replication_bucket\":\"${local.replication_bucket}\"}"
  ev_value           = "{\"canonical_user_id\":\"${data.aws_canonical_user_id.current.id}\"}"
  ev_final_value     = terraform.workspace == "development" ? local.ev_value_dev : local.ev_value
}

data "aws_canonical_user_id" "current" {}

resource "aws_ssm_parameter" "environment_variables" {
  provider = aws.management
  name     = "${local.ssm_prefix}/environment_variables"
  type     = "String"
  value    = local.ev_final_value

  tags = var.default_tags
}
