data "aws_ssm_parameter" "sirius_api_base_uri" {
  name = format("/%s", join("/", compact([local.account.secrets_prefix, "sirius-api-base-uri"])))
}

resource "aws_ssm_parameter" "flag_document_sync" {
  name  = format("/%s", join("/", compact([local.environment, "flag/document-sync"])))
  type  = "String"
  value = "1"

  lifecycle {
    ignore_changes = [value]
  }
}
