data "aws_lambda_function" "redeployer_lambda" {
  function_name = "redeployer"
}

resource "aws_cloudwatch_event_rule" "redeploy_file_scanner" {
  name                = "redeploy-file-scanner-${local.environment}"
  description         = "Redeploy the file scanner to use latest virus definitions"
  schedule_expression = "cron(0 1 * * ? *)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "redeploy_file_scanner" {
  rule  = aws_cloudwatch_event_rule.redeploy_file_scanner.name
  arn   = data.aws_lambda_function.redeployer_lambda.arn
  input = <<EOF
    {
      "cluster": "${aws_ecs_cluster.main.name}",
      "service": "${aws_ecs_service.scan.name}"
    }
  EOF
}

resource "aws_lambda_permission" "allow_cloudwatch_call_lambda" {
  statement_id  = "AllowExecutionFrom-${aws_cloudwatch_event_rule.redeploy_file_scanner.name}"
  action        = "lambda:InvokeFunction"
  function_name = data.aws_lambda_function.redeployer_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.redeploy_file_scanner.arn
}
