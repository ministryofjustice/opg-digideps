module "environment_secrets" {
  for_each = var.account.environments

  source      = "./modules/environment_secrets"
  environment = each.value
  secrets = [
    "api-secret",
    "admin-api-client-secret",
    "admin-frontend-secret",
    "database-password",
    "front-api-client-secret",
    "front-frontend-secret",
    "front-notify-api-key",
    "synchronisation-jwt-token",
    "public-jwt-key-base64",
    "private-jwt-key-base64",
    "smoke-test-variables"
  ]
  tags = var.default_tags
}

module "development_environment_secrets" {
  count = var.account.name == "development" ? 1 : 0

  source      = "./modules/environment_secrets"
  environment = var.account.name
  secrets = [
    "browserstack-username",
    "browserstack-access-key"
  ]
  tags = var.default_tags
}

# Account wide secrets
resource "aws_secretsmanager_secret" "cloud9_users" {
  name        = "cloud9-users"
  description = "Digideps team Cloud9 users"
  tags        = var.default_tags
}

data "aws_secretsmanager_secret_version" "cloud9_users" {
  secret_id = aws_secretsmanager_secret.cloud9_users.id
}

resource "aws_secretsmanager_secret" "slack_webhook_url" {
  name        = "slack-webhook-url"
  description = "URL of webhook for Slack Integration"
  tags        = var.default_tags
}
