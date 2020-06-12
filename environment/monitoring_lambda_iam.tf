resource "aws_iam_role" "monitoring_lambda_role" {
  name_prefix        = "monitoring-${local.environment}-"
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

resource "aws_iam_role_policy" "monitoring_lambda" {
  name   = "monitoring-lambda-${local.environment}"
  role   = aws_iam_role.monitoring_lambda_role.id
  policy = data.aws_iam_policy_document.monitoring_lambda.json
}

data "aws_iam_policy_document" "monitoring_lambda" {
  statement {
    sid       = "allowLogging"
    effect    = "Allow"
    resources = [aws_cloudwatch_log_group.monitoring_lambda.arn]
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogStreams"
    ]
  }

  statement {
    sid       = "allowMetrics"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "cloudwatch:PutMetricData"
    ]
  }

  statement {
    sid       = "allowGetDBSecret"
    effect    = "Allow"
    resources = [data.aws_secretsmanager_secret.database_password.arn]

    actions = [
      "secretsmanager:GetResourcePolicy",
      "secretsmanager:GetSecretValue",
      "secretsmanager:DescribeSecret",
      "secretsmanager:ListSecretVersionIds"
    ]
  }
}


resource "aws_iam_role_policy_attachment" "vpc_access_execution_role" {
  role       = aws_iam_role.monitoring_lambda_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSLambdaVPCAccessExecutionRole"
}
