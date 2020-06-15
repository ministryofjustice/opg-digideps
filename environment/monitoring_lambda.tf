resource "aws_cloudwatch_log_group" "monitoring_lambda" {
  name = "/aws/lambda/monitoring-${local.environment}"
  tags = local.default_tags
}

resource "aws_lambda_function" "monitoring" {
  filename         = data.archive_file.monitoring_lambda_zip.output_path
  source_code_hash = data.archive_file.monitoring_lambda_zip.output_base64sha256
  function_name    = "monitoring-api-${local.environment}"
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
  environment {
    variables = {
      ENVIRONMENT = local.environment
      DB_ENDPOINT = local.account.always_on ? aws_db_instance.api[0].endpoint : aws_rds_cluster.api[0].endpoint
      DB_USER     = "digidepsmaster"
      DB_PORT     = "5432"
      DB_NAME     = "api"
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
  filename = "../lambda_functions/requirements/requirements.txt"
}

data "archive_file" "monitoring_lambda_zip" {
  type        = "zip"
  source_dir  = "../lambda_functions/functions/monitoring"
  output_path = "./monitoring_lambda.zip"
}

data "archive_file" "monitoring_lambda_layer_zip" {
  type        = "zip"
  source_dir  = "../lambda_functions/layers/monitoring"
  output_path = "./monitoring_lambda_layer.zip"
}
