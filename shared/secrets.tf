data "aws_secretsmanager_secret" "slack_webhook_url" {
  name = "slack-webhook-url"
}

data "aws_secretsmanager_secret_version" "slack_webhook_url" {
  secret_id = data.aws_secretsmanager_secret.slack_webhook_url.id
}
