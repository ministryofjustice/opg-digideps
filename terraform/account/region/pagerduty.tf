resource "aws_secretsmanager_secret" "pagerduty_integration_key" {
  count = var.account.name == "production" ? 1 : 0

  name        = "pagerduty-integration-key"
  description = "PagerDuty Amazon CloudWatch integration key"
  kms_key_id  = module.secret_kms.eu_west_1_target_key_arn
  tags        = var.default_tags
}

data "aws_secretsmanager_secret_version" "pagerduty_integration_key" {
  count = local.pagerduty_is_enabled ? 1 : 0

  secret_id = aws_secretsmanager_secret.pagerduty_integration_key[0].id
}

locals {
  pagerduty_is_enabled = var.account.name == "production" && var.account.pagerduty_enabled
  pagerduty_endpoint   = local.pagerduty_is_enabled ? "https://events.pagerduty.com/integration/${trimspace(data.aws_secretsmanager_secret_version.pagerduty_integration_key[0].secret_string)}/enqueue" : null
}

resource "aws_sns_topic_subscription" "pagerduty_alerts" {
  count = local.pagerduty_is_enabled ? 1 : 0

  topic_arn              = aws_sns_topic.alerts.arn
  protocol               = "https"
  endpoint_auto_confirms = true
  endpoint               = local.pagerduty_endpoint
  raw_message_delivery   = false
}

resource "aws_sns_topic_subscription" "pagerduty_availability_alerts" {
  provider = aws.global
  count    = local.pagerduty_is_enabled ? 1 : 0

  topic_arn              = aws_sns_topic.availability-alert.arn
  protocol               = "https"
  endpoint_auto_confirms = true
  endpoint               = local.pagerduty_endpoint
  raw_message_delivery   = false
}
