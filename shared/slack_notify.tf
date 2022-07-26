resource "aws_sns_topic" "alerts" {
  name = "alerts"
  tags = merge(
    local.default_tags,
    { Name = "alerts-${local.account.name}" },
  )
}

#tfsec:ignore:aws-lambda-restrict-source-arn - access is actually restricted to single resource
#tfsec:ignore:aws-lambda-enable-tracing - no control over this
module "notify_slack" {
  source = "github.com/terraform-aws-modules/terraform-aws-notify-slack.git?ref=v5.1.0"

  sns_topic_name   = aws_sns_topic.alerts.name
  create_sns_topic = false

  lambda_function_name = "notify-slack"

  cloudwatch_log_group_retention_in_days = 14

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_webhook_url.secret_string
  slack_channel     = local.account.name == "production" ? "#opg-digideps-team" : "#opg-digideps-devs"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}

resource "aws_sns_topic" "availability-alert" {
  provider     = aws.us-east-1
  name         = "availability-alert"
  display_name = "${local.default_tags["application"]} ${local.default_tags["environment-name"]} Availability Alert"
  tags = merge(
    local.default_tags,
    { Name = "availability-alert-${local.account.name}" },
  )
}

#tfsec:ignore:aws-lambda-restrict-source-arn - access is actually restricted to single resource
#tfsec:ignore:aws-lambda-enable-tracing - no control over this
module "notify_slack_us-east-1" {
  source = "github.com/terraform-aws-modules/terraform-aws-notify-slack.git?ref=v5.1.0"

  providers = {
    aws = aws.us-east-1
  }

  sns_topic_name   = aws_sns_topic.availability-alert.name
  create_sns_topic = false
  create           = local.account.name != "development"

  lambda_function_name = "notify-slack"

  cloudwatch_log_group_retention_in_days = 14

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_webhook_url.secret_string
  slack_channel     = local.account.name == "production" ? "#opg-digideps-team" : "#opg-digideps-devs"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}
