# Following checks are for business logic
resource "aws_cloudwatch_event_rule" "business_functionality_check" {
  name                = "check-business-functionality-${terraform.workspace}"
  description         = "Execute the business functionality comparison check ${terraform.workspace}"
  schedule_expression = "cron(0 8-22 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "business_functionality_check" {
  target_id = "check-business-functionality-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.business_functionality_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name  = "business_functionality_check"
        log-group = terraform.workspace,
        log-entries = [
          "{\"name\":\"submissions_check\",\"search1\":\"/*/*/review\",\"method1\":\"GET\",\"search2\":\"/*/*/declaration\",\"method2\":\"GET\",\"percentage_threshold\":\"0\",\"count_threshold\":\"20\"}",
          "{\"name\":\"registration_check\",\"search1\":\"/register\",\"method1\":\"POST\",\"search2\":\"/*/activate/*\",\"method2\":\"GET\",\"percentage_threshold\":\"0\",\"count_threshold\":\"10\"}",
          "{\"name\":\"authentication_check\",\"search1\":\"/login\",\"method1\":\"POST\",\"search2\":\"/lay|/org/\",\"method2\":\"GET\",\"percentage_threshold\":\"0\",\"count_threshold\":\"100\"}",
        ],
        search-timespan            = "1 hour",
        bank-holidays              = "true",
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}
