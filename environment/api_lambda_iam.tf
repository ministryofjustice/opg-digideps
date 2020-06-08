resource "aws_iam_role" "monitoring_lambda_role" {
  name_prefix        = "lambda-lpa-codes-${local.environment}-"
  assume_role_policy = data.aws_iam_policy_document.lambda_assume.json
  lifecycle {
    create_before_destroy = true
  }
  tags = local.default_tags
}

data "aws_iam_policy_document" "lambda_assume" {
  statement {
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["lambda.amazonaws.com"]
    }
  }
}

resource "aws_iam_role_policy" "lambda" {
  name   = "monitoring-lambda-${local.environment}"
  role   = aws_iam_role.monitoring_lambda_role.id
  policy = data.aws_iam_policy_document.lambda.json
}

data "aws_iam_policy_document" "lambda" {
  statement {
    sid       = "allowLogging"
    effect    = "Allow"
    resources = [aws_cloudwatch_log_group.lambda.arn]
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogStreams"
    ]
  }
}

resource "aws_iam_role_policy_attachment" "vpc_access_execution_role" {
  role       = aws_iam_role.monitoring_lambda_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSLambdaVPCAccessExecutionRole"
}
