resource "aws_lambda_function" "monitoring" {
  filename         = data.archive_file.monitoring_lambda_zip.output_path
  source_code_hash = data.archive_file.monitoring_lambda_zip.output_base64sha256
  function_name    = "monitoring-${local.environment}"
  role             = aws_iam_role.monitoring_lambda_role.arn
  handler          = "monitoring.lambda_handler"
  runtime          = "python3.7"
  timeout          = 5
  depends_on       = [aws_cloudwatch_log_group.monitoring_lambda]
  layers           = [aws_lambda_layer_version.monitoring_lambda_layer.arn]
  vpc_config {
    security_group_ids = [module.monitoring_lambda_security_group.id]
    subnet_ids         = data.aws_subnet.private.*.id
  }
  tracing_config {
    mode = "Active"
  }
  environment {
    variables = {
      ENVIRONMENT = local.environment
      DB_ENDPOINT = local.db.endpoint
      DB_USER     = local.db.username
      DB_PORT     = local.db.port
      DB_NAME     = local.db.name
      SECRET_NAME = data.aws_secretsmanager_secret.database_password.name
    }
  }
  tags = local.default_tags
}

resource "aws_lambda_layer_version" "monitoring_lambda_layer" {
  filename         = data.archive_file.monitoring_lambda_layer_zip.output_path
  source_code_hash = data.archive_file.monitoring_lambda_layer_zip.output_base64sha256
  layer_name       = "requirement_${local.environment}"

  compatible_runtimes = ["python3.7"]

  lifecycle {
    ignore_changes = [
      source_code_hash
    ]
  }
}

data "local_file" "requirements" {
  filename = "../lambdas/requirements/requirements.txt"
}

data "archive_file" "monitoring_lambda_zip" {
  type        = "zip"
  source_dir  = "../lambdas/functions/monitoring"
  output_path = "./monitoring_lambda.zip"
}

data "archive_file" "monitoring_lambda_layer_zip" {
  type        = "zip"
  source_dir  = "../lambdas/layers/monitoring"
  output_path = "./monitoring_lambda_layer.zip"
}
