locals {
  monitor_notify_lambda_function_name = "monitor-notify"
}

# INFO - Lambda used to monitor for different types of events and to notify us via slack
resource "aws_lambda_function" "monitor_notify_lambda" {
  filename      = data.archive_file.monitor_notify_zip.output_path
  function_name = local.monitor_notify_lambda_function_name
  role          = aws_iam_role.lambda_monitor_notify.arn
  handler       = "monitor_notify.lambda_handler"
  runtime       = "python3.11"
  layers        = [aws_lambda_layer_version.lambda_layer.arn]
  depends_on    = [aws_cloudwatch_log_group.monitor_notify_lambda]
  timeout       = 300
  environment {
    variables = {
      PAUSE_NOTIFICATIONS = "0"
    }
  }
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.monitor_notify_zip.output_path)
  tags = merge(
    var.default_tags,
    { Name = "monitor-notify-${var.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "monitor_notify_lambda" {
  name              = "/aws/lambda/${local.monitor_notify_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-monitor-notify-log-group" },
  )
}

resource "aws_iam_role" "lambda_monitor_notify" {
  assume_role_policy = data.aws_iam_policy_document.lambda_slack_policy.json
  name               = "lambda_monitor_notify"
  tags               = var.default_tags
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

resource "aws_iam_role_policy" "lambda_monitor_notify" {
  name   = "lambda-monitor_notify"
  policy = data.aws_iam_policy_document.lambda_monitor_notify.json
  role   = aws_iam_role.lambda_monitor_notify.id
}

data "aws_iam_policy_document" "lambda_monitor_notify" {
  statement {
    sid    = "allowLogging"
    effect = "Allow"
    resources = [
      aws_cloudwatch_log_group.monitor_notify_lambda.arn,
      "${aws_cloudwatch_log_group.monitor_notify_lambda.arn}:*"
    ]
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogStreams"
    ]
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
      "SNS:Subscribe",
      "SNS:Receive",
    ]
    resources = [
      aws_sns_topic.alerts.arn,
      aws_sns_topic.availability-alert.arn
    ]
  }

  statement {
    sid    = "SnsDecryptKms"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      module.sns_kms.eu_west_1_target_key_arn
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

data "archive_file" "monitor_notify_zip" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/monitor_notify_lambda/app"
  output_path = "../../lambdas/functions/monitor_notify_lambda/monitor_notify.zip"
}

data "archive_file" "monitor_notify_layer" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/monitor_notify_lambda/layers"
  output_path = "../../lambdas/functions/monitor_notify_lambda/layer.zip"
}

resource "aws_lambda_layer_version" "lambda_layer" {
  filename         = data.archive_file.monitor_notify_layer.output_path
  source_code_hash = data.archive_file.monitor_notify_layer.output_base64sha256
  layer_name       = "monitor_notify_layer"

  compatible_runtimes = ["python3.11"]

  lifecycle {
    ignore_changes = [
      source_code_hash
    ]
  }
}

resource "aws_lambda_permission" "sns" {
  statement_id  = "AllowExecutionFromSNSTopic"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.monitor_notify_lambda.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.alerts.arn
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.monitor_notify_lambda
    ]
  }
}

resource "aws_lambda_permission" "scheduled_checks" {
  statement_id  = "AllowExecutionFromScheduledCheck"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.monitor_notify_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = "arn:aws:events:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:rule/check-*"
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.monitor_notify_lambda
    ]
  }
}

resource "aws_lambda_permission" "sns_availability" {
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.monitor_notify_lambda.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.availability-alert.arn
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.monitor_notify_lambda
    ]
  }
}
