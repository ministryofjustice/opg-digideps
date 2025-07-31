# Anomaly detection checks

resource "aws_cloudwatch_event_rule" "anomaly_detection" {
  name                = "check-anomaly-detection-${terraform.workspace}"
  description         = "Execute the anomaly detection check for ${terraform.workspace}"
  schedule_expression = "cron(10 09 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "anomaly_detection" {
  target_id = "check-anomaly-detection-${terraform.workspace}"
  arn       = data.aws_lambda_function.monitor_notify_lambda.arn
  rule      = aws_cloudwatch_event_rule.anomaly_detection.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name           = "anomaly_detection_check"
        channel-identifier = "devs",
      }
    }
  )
}
