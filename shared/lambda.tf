locals {
  redeployer_lambda_function_name = "redeployer"
}

resource "aws_iam_role" "lambda_redeployer" {
  assume_role_policy = data.aws_iam_policy_document.lambda_redeployer_policy.json
  name               = "lambda_redeployer"
  tags               = local.default_tags
}

data "aws_iam_policy_document" "lambda_redeployer_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["lambda.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "lambda_redeployer" {
  name   = "lambda_redeployer"
  policy = data.aws_iam_policy_document.lambda_redeployer.json
  role   = aws_iam_role.lambda_redeployer.id
}

data "aws_iam_policy_document" "lambda_redeployer" {
  statement {
    sid    = "UpdateService"
    effect = "Allow"
    actions = [
      "ecs:UpdateService",
    ]
    resources = [
      "*",
    ]
  }

  statement {
    sid    = "WriteLogs"
    effect = "Allow"
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
    ]
    resources = [
      "*",
    ]
  }
}

data "archive_file" "redeployer_zip" {
  type        = "zip"
  source_file = "${path.module}/go_redeployer/main"
  output_path = "${path.module}/go_redeployer/function.zip"
}

resource "aws_lambda_function" "redeployer_lambda" {
  filename      = data.archive_file.redeployer_zip.output_path
  function_name = local.redeployer_lambda_function_name
  role          = aws_iam_role.lambda_redeployer.arn
  handler       = "main"
  runtime       = "go1.x"
  depends_on    = [aws_cloudwatch_log_group.redeployer_lambda]
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.redeployer_zip.output_path)
  tags = merge(
    local.default_tags,
    { Name = "redeployer-${local.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "redeployer_lambda" {
  name              = "/aws/lambda/${local.redeployer_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  tags = merge(
    local.default_tags,
    { Name = "redeployer-${local.account.name}-log-group" },
  )
}
