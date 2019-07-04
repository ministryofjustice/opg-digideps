data "aws_kms_alias" "secretmanager" {
  name = "alias/aws/secretsmanager"
}

data "aws_secretsmanager_secret" "database_password" {
  name = "${terraform.workspace}/database-password"
}

data "aws_secretsmanager_secret_version" "database_password" {
  secret_id = data.aws_secretsmanager_secret.database_password.id
}

data "aws_secretsmanager_secret" "registry" {
  name = "${terraform.workspace}/registry"
}

data "aws_secretsmanager_secret" "api_secret" {
  name = "${terraform.workspace}/api-secret"
}

data "aws_secretsmanager_secret" "admin_frontend_secret" {
  name = "${terraform.workspace}/admin-frontend-secret"
}

data "aws_secretsmanager_secret" "front_frontend_secret" {
  name = "${terraform.workspace}/front-frontend-secret"
}

data "aws_secretsmanager_secret" "oauth2_client_secret" {
  name = "${terraform.workspace}/oauth2-client-secret"
}

data "aws_secretsmanager_secret" "admin_api_client_secret" {
  name = "${terraform.workspace}/admin-api-client-secret"
}

data "aws_secretsmanager_secret" "front_api_client_secret" {
  name = "${terraform.workspace}/front-api-client-secret"
}

data "aws_secretsmanager_secret" "google_analytics" {
  name = "${terraform.workspace}/google-analytics"
}

