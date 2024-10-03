resource "aws_sns_topic" "alerts" {
  name = "alerts"
  tags = merge(
    var.default_tags,
    { Name = "alerts-${var.account.name}" },
  )
}

resource "aws_sns_topic" "availability-alert" {
  provider     = aws.global
  name         = "availability-alert-${local.current_main_region}"
  display_name = "${var.default_tags["application"]} ${var.default_tags["environment-name"]} Availability Alert"
  tags = merge(
    var.default_tags,
    { Name = "availability-alert-${var.account.name}-${local.current_main_region}" },
  )
}

resource "aws_sns_topic_subscription" "subscription" {
  endpoint  = aws_lambda_function.monitor_notify_lambda.arn
  protocol  = "lambda"
  topic_arn = aws_sns_topic.alerts.arn
}

resource "aws_sns_topic_subscription" "subscription_availability" {
  provider = aws.global

  endpoint  = aws_lambda_function.monitor_notify_lambda.arn
  protocol  = "lambda"
  topic_arn = aws_sns_topic.availability-alert.arn
}
