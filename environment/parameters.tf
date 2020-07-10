locals {
  feature_flag_prefix = "/${local.environment}/flag/"
  parameter_prefix    = "/${local.environment}/parameter/"
}

data "aws_ssm_parameter" "sirius_api_base_uri" {
  name = format("/%s", join("/", compact([local.account.secrets_prefix, "sirius-api-base-uri"])))
}

resource "aws_ssm_parameter" "document_sync_row_limit" {
  name  = "${local.parameter_prefix}document-sync-row-limit"
  type  = "String"
  value = "100"

  tags = local.default_tags

  lifecycle {
    ignore_changes = [value]
  }
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

resource "aws_ssm_parameter" "checklist_sync_row_limit" {
  name  = "${local.parameter_prefix}checklist-sync-row-limit"
  type  = "String"
  value = "30"

  tags = local.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}

resource "aws_ssm_parameter" "flag_checklist_sync" {
  name  = "${local.feature_flag_prefix}checklist-sync"
  type  = "String"
  value = "1"

  tags = local.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}
