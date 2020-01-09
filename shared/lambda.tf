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
}

data "archive_file" "redeployer_zip" {
  type        = "zip"
  source_file = "${path.module}/../ecs_helper/main"
  output_path = "${path.module}/../ecs_helper/redeployer.zip"
}

resource "aws_lambda_function" "redeployer_lambda" {
  filename      = data.archive_file.redeployer_zip.output_path
  function_name = "redeployer"
  role          = aws_iam_role.lambda_redeployer.arn
  handler       = "main"
  runtime       = "go1.x"

  source_code_hash = filebase64sha256(data.archive_file.redeployer_zip.output_path)
}
