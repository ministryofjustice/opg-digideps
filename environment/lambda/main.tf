resource "aws_cloudwatch_log_group" "lambda" {
  name       = "/aws/lambda/${var.lambda_name}"
  kms_key_id = var.logs_kms_key_arn
  tags       = var.tags
}

resource "aws_lambda_function" "lambda_function" {
  function_name = var.lambda_name
  image_uri     = var.image_uri
  package_type  = var.package_type
  role          = aws_iam_role.lambda_role.arn
  timeout       = var.timeout
  memory_size   = var.memory
  depends_on    = [aws_cloudwatch_log_group.lambda]

  vpc_config {
    subnet_ids         = var.aws_subnet_ids
    security_group_ids = [aws_security_group.lambda.id]
  }

  tracing_config {
    mode = "Active"
  }

  dynamic "environment" {
    for_each = length(keys(var.environment_variables)) == 0 ? [] : [true]
    content {
      variables = var.environment_variables
    }
  }
}

resource "aws_lambda_permission" "lambda_permission" {
  count         = var.api_gateway_access ? 1 : 0
  statement_id  = "AllowApiDeputyReportingGatewayInvoke_${var.environment}_${var.api_version}"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.lambda_function.function_name
  principal     = "apigateway.amazonaws.com"

  source_arn = "${var.rest_api.execution_arn}/*/*/*"
}
