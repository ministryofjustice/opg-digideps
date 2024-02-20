locals {
  feature_flag_prefix       = "/${local.environment}/flag/"
  parameter_prefix          = "/${local.environment}/parameter/"
  sirius_api_base_uri_value = local.environment == "production02" ? "https://deputy-reporting.api.opg.service.justice.gov.uk" : "http://mock-sirius-integration:8080"
}

resource "aws_ssm_parameter" "sirius_api_base_uri" {
  name  = "${local.parameter_prefix}sirius-api-base-uri"
  type  = "String"
  value = local.sirius_api_base_uri_value

  tags = var.default_tags
}

resource "aws_ssm_parameter" "document_sync_row_limit" {
  name  = "${local.parameter_prefix}document-sync-row-limit"
  type  = "String"
  value = "100"

  tags = var.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}

resource "aws_ssm_parameter" "flag_document_sync" {
  name  = "${local.feature_flag_prefix}document-sync"
  type  = "String"
  value = "1"

  tags = var.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}

resource "aws_ssm_parameter" "checklist_sync_row_limit" {
  name  = "${local.parameter_prefix}checklist-sync-row-limit"
  type  = "String"
  value = "30"

  tags = var.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}

resource "aws_ssm_parameter" "flag_checklist_sync" {
  name  = "${local.feature_flag_prefix}checklist-sync"
  type  = "String"
  value = "1"

  tags = var.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}

resource "aws_ssm_parameter" "flag_paper_reports" {
  name  = "${local.feature_flag_prefix}paper-reports"
  type  = "String"
  value = "0"

  tags = var.default_tags

  lifecycle {
    ignore_changes = [value]
  }
}
