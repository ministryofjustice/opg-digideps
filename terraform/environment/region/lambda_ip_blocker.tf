locals {
  block_ip_lambda_function_name = "block-ip"
}

# INFO - Lambda used to manage all our block_ip notifications
resource "aws_lambda_function" "block_ip_lambda" {
  filename      = data.archive_file.block_ip_zip.output_path
  function_name = local.block_ip_lambda_function_name
  role          = aws_iam_role.lambda_block_ip.arn
  handler       = "block_ip.lambda_handler"
  runtime       = "python3.11"
  #  layers        = [aws_lambda_layer_version.lambda_layer.arn]
  depends_on = [aws_cloudwatch_log_group.block_ip_lambda]
  timeout    = 300
  environment {
    variables = {
      ENVIRONMENT = local.environment
    }
  }
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.block_ip_zip.output_path)
  tags = merge(
    var.default_tags,
    { Name = "block-ip-${var.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "block_ip_lambda" {
  name              = "/aws/lambda/${local.block_ip_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-block_ip-log-group" },
  )
}

resource "aws_iam_role" "lambda_block_ip" {
  assume_role_policy = data.aws_iam_policy_document.lambda_block_ip_policy.json
  name               = "lambda-block-ips"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "lambda_block_ip_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["lambda.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "lambda_block_ip" {
  name   = "lambda-block-ip"
  policy = data.aws_iam_policy_document.lambda_block_ip.json
  role   = aws_iam_role.lambda_block_ip.id
}

data "aws_iam_policy_document" "lambda_block_ip" {
  statement {
    sid    = "allowLogging"
    effect = "Allow"
    resources = [
      aws_cloudwatch_log_group.block_ip_lambda.arn,
      "${aws_cloudwatch_log_group.block_ip_lambda.arn}:*"
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
    sid    = "ReadWriteTable"
    effect = "Allow"
    resources = [
      aws_dynamodb_table.blocked_ips_table.arn,
    ]
    actions = [
      "dynamodb:BatchGet*",
      "dynamodb:DescribeStream",
      "dynamodb:DescribeTable",
      "dynamodb:Get*",
      "dynamodb:Query",
      "dynamodb:Scan",
      "dynamodb:BatchWrite*",
      "dynamodb:Delete*",
      "dynamodb:Update*",
      "dynamodb:PutItem"
    ]
  }

  statement {
    sid    = "UpdateIPSet"
    effect = "Allow"
    actions = [
      "waf:ListIPSets",
      "waf:GetIPSet",
      "waf:UpdateIPSet"
    ]
    resources = ["*"]
  }

}

data "archive_file" "block_ip_zip" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/block_ips_lambda/app"
  output_path = "../../lambdas/functions/block_ips_lambda/block_ips.zip"
}

#data "archive_file" "block_ip_layer" {
#  type        = "zip"
#  source_dir  = "../../lambdas/functions/block_ip_lambda/layers"
#  output_path = "../../lambdas/functions/block_ip_lambda/layer.zip"
#}

#resource "aws_lambda_layer_version" "lambda_layer" {
#  filename         = data.archive_file.block_ip_layer.output_path
#  source_code_hash = data.archive_file.block_ip_layer.output_base64sha256
#  layer_name       = "block_ip_notify_layer"
#
#  compatible_runtimes = ["python3.11"]
#
#  lifecycle {
#    ignore_changes = [
#      source_code_hash
#    ]
#  }
#}

#resource "aws_lambda_permission" "sns" {
#  statement_id  = "AllowExecutionFromSNSTopic"
#  action        = "lambda:InvokeFunction"
#  function_name = aws_lambda_function.block_ip_lambda.function_name
#  principal     = "sns.amazonaws.com"
#  source_arn    = aws_sns_topic.alerts.arn
#  lifecycle {
#    replace_triggered_by = [
#      aws_lambda_function.block_ip_lambda
#    ]
#  }
#}

resource "aws_lambda_permission" "scheduled_checks" {
  statement_id  = "AllowExecutionFromScheduledCheck"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.block_ip_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = "arn:aws:events:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:rule/block-ips"
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.block_ip_lambda
    ]
  }
}

#resource "aws_lambda_permission" "sns_availability" {
#  action        = "lambda:InvokeFunction"
#  function_name = aws_lambda_function.block_ip_lambda.function_name
#  principal     = "sns.amazonaws.com"
#  source_arn    = aws_sns_topic.availability-alert.arn
#  lifecycle {
#    replace_triggered_by = [
#      aws_lambda_function.block_ip_lambda
#    ]
#  }
#}
