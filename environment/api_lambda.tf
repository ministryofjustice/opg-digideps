resource "aws_cloudwatch_log_group" "lambda" {
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
  depends_on       = [aws_cloudwatch_log_group.lambda]
  layers           = [aws_lambda_layer_version.monitoring_lambda_layer.arn]
  vpc_config {
    security_group_ids = [module.api_service_security_group.id]
    subnet_ids         = data.aws_subnet.private.*.id
  }
  environment {
    variables = {
      LOGGER_LEVEL = "INFO"
      ENVIRONMENT  = local.environment
    }
  }
  tags = local.default_tags
}

//resource "aws_lambda_permission" "lambda_permission" {
//  statement_id  = "AllowApiLPACodesGatewayInvoke-${var.environment}-${var.openapi_version}-${var.lambda_prefix}"
//  action        = "lambda:InvokeFunction"
//  function_name = aws_lambda_function.lambda_function.function_name
//  principal     = "apigateway.amazonaws.com"
//
//  source_arn = "${var.rest_api.execution_arn}/*/*/*"
//}


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
  filename = "../lambda/requirements/requirements.txt"
}

data "archive_file" "monitoring_lambda_zip" {
  type        = "zip"
  source_dir  = "../lambda/functions/monitoring"
  output_path = "./monitoring_lambda.zip"
}

data "archive_file" "monitoring_lambda_layer_zip" {
  type        = "zip"
  source_dir  = "../lambda/layers/monitoring"
  output_path = "./monitoring_lambda_layer.zip"
}
