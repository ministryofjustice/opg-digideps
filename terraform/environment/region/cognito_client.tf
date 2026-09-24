data "aws_cognito_user_pools" "deputy_reporting_admin" {
  provider = aws.identity
  name     = "deputy-reporting-admin-admin"
}

data "aws_ssm_parameter" "deputy_reporting_admin_domain" {
  provider = aws.identity
  name     = "/digideps/cognito/admin/domain"
}

locals {
  admin_cognito_user_pool_id          = tolist(data.aws_cognito_user_pools.deputy_reporting_admin.ids)[0]
  admin_cognito_user_pool_domain_name = "https://${data.aws_ssm_parameter.deputy_reporting_admin_domain.value}.auth.eu-west-1.amazoncognito.com"
}

resource "aws_cognito_user_pool_client" "deputy_reporting_admin" {
  provider                             = aws.identity
  name                                 = "${local.environment}-admin-auth"
  user_pool_id                         = local.admin_cognito_user_pool_id
  allowed_oauth_flows                  = ["code"]
  allowed_oauth_scopes                 = ["openid"]
  supported_identity_providers         = ["EntraID"]
  allowed_oauth_flows_user_pool_client = true
  explicit_auth_flows = [
    "ALLOW_CUSTOM_AUTH",
    "ALLOW_REFRESH_TOKEN_AUTH",
    "ALLOW_USER_SRP_AUTH",
  ]

  generate_secret = true

  token_validity_units {
    access_token  = "minutes"
    id_token      = "seconds"
    refresh_token = "days"
  }

  access_token_validity  = 5
  id_token_validity      = 3600
  refresh_token_validity = 1
  read_attributes        = []
  write_attributes       = []

  callback_urls = ["https://${var.admin_fully_qualified_domain_name}/oauth2/idpresponse"]
  logout_urls   = ["https://${var.admin_fully_qualified_domain_name}/"]
}
