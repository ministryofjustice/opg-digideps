locals {
  slack_lambda_function_name = "slack-notifier"
}

resource "aws_lambda_function" "slack_lambda" {
  filename      = data.archive_file.slack_zip.output_path
  function_name = local.slack_lambda_function_name
  role          = aws_iam_role.lambda_slack.arn
  handler       = "slack.lambda_handler"
  runtime       = "python3.11"
  layers        = [aws_lambda_layer_version.lambda_layer.arn]
  depends_on    = [aws_cloudwatch_log_group.slack_lambda]
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.slack_zip.output_path)
  tags = merge(
    local.default_tags,
    { Name = "slack-${local.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "slack_lambda" {
  name              = "/aws/lambda/${local.slack_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags = merge(
    local.default_tags,
    { Name = "${local.account.name}-slack-log-group" },
  )
}

resource "aws_iam_role" "lambda_slack" {
  assume_role_policy = data.aws_iam_policy_document.lambda_slack_policy.json
  name               = "lambda_slack"
  tags               = local.default_tags
}

data "aws_iam_policy_document" "lambda_slack_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["lambda.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "lambda_slack" {
  name   = "lambda_slack"
  policy = data.aws_iam_policy_document.lambda_slack.json
  role   = aws_iam_role.lambda_slack.id
}

data "aws_iam_policy_document" "lambda_slack" {
  statement {
    sid    = "WriteLogs"
    effect = "Allow"
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogStreams"
    ]
    resources = [aws_cloudwatch_log_group.slack_lambda.arn]
  }

  statement {
    sid    = "ReadLogsAndInsights"
    effect = "Allow"
    actions = [
      "logs:GetLogEvents",
      "logs:StartQuery",
      "logs:StopQuery",
      "logs:GetQueryResults",
    ]
    resources = ["*"]
  }

  statement {
    sid    = "SNS"
    effect = "Allow"
    actions = [
      "sns:Subscribe",
      "sns:Receive"
    ]
    resources = [
      aws_sns_topic.alerts.arn,
      aws_sns_topic.availability-alert.arn
    ]
  }

  statement {
    sid    = "ReadSecret"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue",
    ]
    resources = [aws_secretsmanager_secret.slack_webhook_url.arn]
  }
}

data "archive_file" "slack_zip" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/slack_lambda/app"
  output_path = "../../lambdas/functions/slack_lambda/slack.zip"
}

data "archive_file" "slack_layer" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/slack_lambda/layers"
  output_path = "../../lambdas/functions/slack_lambda/layer.zip"
}

resource "aws_lambda_layer_version" "lambda_layer" {
  filename         = data.archive_file.slack_layer.output_path
  source_code_hash = data.archive_file.slack_layer.output_base64sha256
  layer_name       = "slack_notify_layer"

  compatible_runtimes = ["python3.11"]

  lifecycle {
    ignore_changes = [
      source_code_hash
    ]
  }
}

resource "aws_lambda_permission" "sns" {
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.slack_lambda.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.alerts.arn
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.slack_lambda
    ]
  }
}

resource "aws_sns_topic_subscription" "subscription" {
  endpoint  = aws_lambda_function.slack_lambda.arn
  protocol  = "lambda"
  topic_arn = aws_sns_topic.alerts.arn
}

resource "aws_lambda_permission" "sns_availability" {
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.slack_lambda.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.availability-alert.arn
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.slack_lambda
    ]
  }
}

resource "aws_sns_topic_subscription" "subscription_availability" {
  provider = aws.us-east-1

  endpoint  = aws_lambda_function.slack_lambda.arn
  protocol  = "lambda"
  topic_arn = aws_sns_topic.availability-alert.arn
}