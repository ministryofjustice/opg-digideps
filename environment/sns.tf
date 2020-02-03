data "aws_sns_topic" "alerts" {
  name = "alerts"
}

module "notify_slack" {
  source  = "terraform-aws-modules/notify-slack/aws"
  version = "~> 2.0"

  sns_topic_name   = data.aws_sns_topic.alerts.name
  create_sns_topic = false

  lambda_function_name = "notify-slack-${local.environment}"

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_webhook_url.secret_string
  slack_channel     = local.account.is_production == 1 ? "#opg-digideps-team" : "#opg-digideps-devs"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}

resource "aws_sns_topic_subscription" "availability_sns_alert_slack" {
  topic_arn = data.aws_sns_topic.availability-alert.arn
  protocol  = "lambda"
  endpoint  = module.notify_slack.notify_slack_lambda_function_arn
}

resource "aws_lambda_permission" "availability_sns_alert_slack" {
  statement_id  = "AllowAvailabilityAlertExecutionFromSNS"
  action        = "lambda:InvokeFunction"
  function_name = module.notify_slack.notify_slack_lambda_function_arn
  principal     = "sns.amazonaws.com"
  source_arn    = data.aws_sns_topic.availability-alert.arn
}
