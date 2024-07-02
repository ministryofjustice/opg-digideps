locals {
  block_ips_lambda_function_name = "block-ips"
}

# INFO - Lambda used to manage blocking of IP addresses on the WAF
resource "aws_lambda_function" "block_ips_lambda" {
  filename      = data.archive_file.block_ips_zip.output_path
  function_name = local.block_ips_lambda_function_name
  role          = aws_iam_role.lambda_block_ips.arn
  handler       = "block_ips.lambda_handler"
  runtime       = "python3.11"
  depends_on    = [aws_cloudwatch_log_group.block_ips_lambda]
  timeout       = 300
  environment {
    variables = {
      ENVIRONMENT = var.account.ip_block_workspace
    }
  }
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.block_ips_zip.output_path)
  tags = merge(
    var.default_tags,
    { Name = "block-ip-${var.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "block_ips_lambda" {
  name              = "/aws/lambda/${local.block_ips_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-block_ips-log-group" },
  )
}

resource "aws_iam_role" "lambda_block_ips" {
  assume_role_policy = data.aws_iam_policy_document.lambda_block_ips_policy.json
  name               = "lambda-block-ips"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "lambda_block_ips_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["lambda.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "lambda_block_ips" {
  name   = "lambda-block-ips"
  policy = data.aws_iam_policy_document.lambda_block_ips.json
  role   = aws_iam_role.lambda_block_ips.id
}

data "aws_iam_policy_document" "lambda_block_ips" {
  statement {
    sid    = "allowLogging"
    effect = "Allow"
    resources = [
      aws_cloudwatch_log_group.block_ips_lambda.arn,
      "${aws_cloudwatch_log_group.block_ips_lambda.arn}:*"
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
      "wafv2:ListIPSets",
      "wafv2:GetIPSet",
      "wafv2:UpdateIPSet"
    ]
    resources = ["*"]
  }

}

data "archive_file" "block_ips_zip" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/block_ips_lambda/app"
  output_path = "../../lambdas/functions/block_ips_lambda/block_ips.zip"
}

resource "aws_lambda_permission" "scheduled_block_ip_rule" {
  statement_id  = "AllowExecutionFromScheduledCheck"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.block_ips_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = "arn:aws:events:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:rule/block-ips-*"
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.block_ips_lambda
    ]
  }
}
