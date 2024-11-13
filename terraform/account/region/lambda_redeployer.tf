# INFO - Lambda currently used to redeploy the scan container. Can be used for any nightly redeploy.
locals {
  redeployer_lambda_function_name = "redeployer"
}

resource "aws_lambda_function" "redeployer_lambda" {
  filename      = data.archive_file.redeployer_zip.output_path
  function_name = local.redeployer_lambda_function_name
  role          = aws_iam_role.lambda_redeployer.arn
  handler       = "bootstrap"
  runtime       = "provided.al2"
  depends_on    = [aws_cloudwatch_log_group.redeployer_lambda]
  tracing_config {
    mode = "Active"
  }

  source_code_hash = filebase64sha256(data.archive_file.redeployer_zip.output_path)
  tags = merge(
    var.default_tags,
    { Name = "redeployer-${var.account.name}" },
  )
}

resource "aws_cloudwatch_log_group" "redeployer_lambda" {
  name              = "/aws/lambda/${local.redeployer_lambda_function_name}"
  retention_in_days = 14
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  tags = merge(
    var.default_tags,
    { Name = "redeployer-${var.account.name}-log-group" },
  )
}

resource "aws_iam_role" "lambda_redeployer" {
  assume_role_policy = data.aws_iam_policy_document.lambda_redeployer_policy.json
  name               = "lambda_redeployer"
  tags               = var.default_tags
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
    #tfsec:ignore:aws-iam-no-policy-wildcards - redeployer should have access to update any ecs service
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
  source_file = "${path.module}/go_redeployer/bootstrap"
  output_path = "${path.module}/go_redeployer/function.zip"
}
