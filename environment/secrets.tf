data "aws_kms_alias" "secretmanager" {
  name = "alias/aws/secretsmanager"
}

data "aws_secretsmanager_secret" "database_password" {
  name = "${local.account["secrets_prefix"]}/database-password"
}

data "aws_secretsmanager_secret_version" "database_password" {
  secret_id = data.aws_secretsmanager_secret.database_password.id
}

data "aws_secretsmanager_secret" "api_secret" {
  name = "${local.account["secrets_prefix"]}/api-secret"
}

data "aws_secretsmanager_secret" "front_frontend_secret" {
  name = "${local.account["secrets_prefix"]}/front-frontend-secret"
}

data "aws_secretsmanager_secret" "admin_frontend_secret" {
  name = "${local.account["secrets_prefix"]}/admin-frontend-secret"
}

data "aws_secretsmanager_secret" "oauth2_client_secret" {
  name = "${local.account["secrets_prefix"]}/oauth2-client-secret"
}

data "aws_secretsmanager_secret" "admin_api_client_secret" {
  name = "${local.account["secrets_prefix"]}/admin-api-client-secret"
}

data "aws_secretsmanager_secret" "front_api_client_secret" {
  name = "${local.account["secrets_prefix"]}/front-api-client-secret"
}
