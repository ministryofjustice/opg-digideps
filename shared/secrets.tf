resource "aws_secretsmanager_secret" "slack_webhook_url" {
  name        = "slack-webhook-url"
  description = "URL of webhook for Slack Integration"
  tags        = local.default_tags
}

data "aws_secretsmanager_secret_version" "slack_webhook_url" {
  secret_id = aws_secretsmanager_secret.slack_webhook_url.id
}

module "environment_secrets" {
  for_each = local.account.environments

  source      = "./environment_secrets"
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
    "private-jwt-key-base64"
  ]
  tags = local.default_tags
}

module "development_environment_secrets" {
  count = local.account.name == "development" ? 1 : 0

  source      = "./environment_secrets"
  environment = local.account.name
  secrets = [
    "browserstack-username",
    "browserstack-access-key"
  ]
  tags = local.default_tags
}
