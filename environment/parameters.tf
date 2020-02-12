data "aws_ssm_parameter" "sirius_api_base_uri" {
  name = join("/", compact([local.account.secrets_prefix, "sirius-api-base-uri"]))
}
