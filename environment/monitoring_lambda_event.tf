resource "aws_cloudwatch_event_rule" "monitoring_db_queries" {
  name                = "monitor-rds-${local.environment}"
  description         = "Runs bespoke monitoring SQL statements against RDS DB"
  schedule_expression = "rate(1 hour)"
  tags                = local.default_tags
  is_enabled          = local.account == "production02" ? true : false
}

resource "aws_cloudwatch_event_target" "monitoring_db_queries" {
  rule  = aws_cloudwatch_event_rule.monitoring_db_queries.name
  arn   = aws_lambda_function.monitoring.arn
  input = <<EOF
    {
      "check_name": "queued_documents"
    }
  EOF
}

resource "aws_lambda_permission" "allow_cloudwatch_call_monitor_lambda" {
  statement_id  = "AllowExecutionFrom-${aws_cloudwatch_event_rule.monitoring_db_queries.name}"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.monitoring.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.monitoring_db_queries.arn
}
