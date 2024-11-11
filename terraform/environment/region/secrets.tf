data "aws_kms_alias" "secretmanager" {
  name = "alias/aws/secretsmanager"
}

data "aws_secretsmanager_secret" "database_password" {
  name = join("/", compact([var.secrets_prefix, "database-password"]))
}

data "aws_secretsmanager_secret_version" "database_password" {
  secret_id = data.aws_secretsmanager_secret.database_password.id
}

data "aws_secretsmanager_secret" "api_secret" {
  name = join("/", compact([var.secrets_prefix, "api-secret"]))
}

data "aws_secretsmanager_secret" "front_frontend_secret" {
  name = join("/", compact([var.secrets_prefix, "front-frontend-secret"]))
}

data "aws_secretsmanager_secret" "admin_frontend_secret" {
  name = join("/", compact([var.secrets_prefix, "admin-frontend-secret"]))
}

data "aws_secretsmanager_secret" "admin_api_client_secret" {
  name = join("/", compact([var.secrets_prefix, "admin-api-client-secret"]))
}

data "aws_secretsmanager_secret" "front_api_client_secret" {
  name = join("/", compact([var.secrets_prefix, "front-api-client-secret"]))
}

data "aws_secretsmanager_secret" "front_notify_api_key" {
  name = join("/", compact([var.secrets_prefix, "front-notify-api-key"]))
}

data "aws_secretsmanager_secret" "private_jwt_key_base64" {
  name = join("/", compact([var.secrets_prefix, "private-jwt-key-base64"]))
}

data "aws_secretsmanager_secret" "public_jwt_key_base64" {
  name = join("/", compact([var.secrets_prefix, "public-jwt-key-base64"]))
}

data "aws_secretsmanager_secret" "jwt_token_synchronisation" {
  name = join("/", compact([var.secrets_prefix, "synchronisation-jwt-token"]))
}

data "aws_secretsmanager_secret" "smoke_tests_variables" {
  name = join("/", compact([var.secrets_prefix, "smoke-test-variables"]))
}

data "aws_secretsmanager_secret" "custom_sql_db_password" {
  name = join("/", compact([var.secrets_prefix, "custom-sql-db-password"]))
}

data "aws_secretsmanager_secret" "readonly_sql_db_password" {
  name = join("/", compact([var.secrets_prefix, "readonly-sql-db-password"]))
}

data "aws_secretsmanager_secret" "anonymise-default-pw" {
  name = "anonymisation-default-user-pw"
}

##### Shared Application KMS key for logs #####
#data "aws_kms_alias" "cloudwatch_application_secret_encryption" {
#  name = "alias/digideps_secret_encryption_key"
#}
