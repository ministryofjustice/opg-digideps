locals {
  feature_flag_prefix = "/${local.environment}/flag/"
}

data "aws_ssm_parameter" "sirius_api_base_uri" {
  name = format("/%s", join("/", compact([local.account.secrets_prefix, "sirius-api-base-uri"])))
}

resource "aws_ssm_parameter" "flag_document_sync" {
  name  = "${local.feature_flag_prefix}document-sync"
  type  = "String"
  value = "1"

  tags = local.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}
