locals {
  security_lambda_function_name = "security"
}

# INFO - Lambda used to manage blocking of IP addresses on the WAF
resource "aws_lambda_function" "security_lambda" {
  filename      = data.archive_file.security_zip.output_path
  function_name = local.security_lambda_function_name
  role          = aws_iam_role.lambda_security.arn
  handler       = "security.lambda_handler"
  runtime       = "python3.14"
  depends_on    = [aws_cloudwatch_log_group.security_lambda]
  timeout       = 300
  environment {
    variables = {
      ENVIRONMENT = var.account.ip_block_workspace
    }
  }
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.security_zip.output_path)
  tags = merge(
    var.default_tags,
    { Name = "security-${var.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "security_lambda" {
  name              = "/aws/lambda/${local.security_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  tags = merge(
    var.default_tags,
    { Name = "${var.account.name}-security-log-group" },
  )
}

resource "aws_iam_role" "lambda_security" {
  assume_role_policy   = data.aws_iam_policy_document.lambda_security_policy.json
  name                 = "lambda-security"
  permissions_boundary = data.aws_iam_policy.default_boundary.arn
  tags                 = var.default_tags
}

data "aws_iam_policy_document" "lambda_security_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["lambda.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "lambda_security" {
  name   = "lambda-security"
  policy = data.aws_iam_policy_document.lambda_security.json
  role   = aws_iam_role.lambda_security.id
}

data "aws_iam_policy_document" "lambda_security" {
  statement {
    sid    = "allowLogging"
    effect = "Allow"
    resources = [
      aws_cloudwatch_log_group.security_lambda.arn,
      "${aws_cloudwatch_log_group.security_lambda.arn}:*"
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

  statement {
    sid    = "UseDynamodbKMSKey"
    effect = "Allow"
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey"
    ]
    resources = [
      module.dynamodb_kms.eu_west_1_target_key_arn
    ]
  }
}

data "archive_file" "security_zip" {
  type        = "zip"
  source_dir  = "../../lambdas/functions/security_lambda/app"
  output_path = "../../lambdas/functions/security_lambda/security.zip"
}

resource "aws_lambda_permission" "scheduled_security_rule" {
  statement_id  = "AllowExecutionFromScheduledCheck"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.security_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = "arn:aws:events:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:rule/security-*"
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.security_lambda
    ]
  }
}
