data "aws_lambda_function" "redeployer_lambda" {
  function_name = "redeployer"
}

resource "aws_cloudwatch_event_rule" "redeploy_file_scanner" {
  name                = "redeploy-file-scanner-${local.environment}"
  description         = "Redeploy the file scanner to use latest virus definitions"
  schedule_expression = "rate(12 hours)"
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
