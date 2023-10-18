data "aws_lambda_function" "slack_lambda" {
  function_name = "slack-notifier"
}

# Cross account DR backup

resource "aws_cloudwatch_event_rule" "cross_account_backup_check" {
  name                = "backup-cross-account-check-${terraform.workspace}"
  description         = "Execute the cross account DR backup check for ${terraform.workspace}"
  schedule_expression = "cron(10 09 * * ? *)"
  is_enabled          = true
}

resource "aws_cloudwatch_event_target" "cross_account_backup_check" {
  target_id = "backup-cross-account-${terraform.workspace}"
  arn       = data.aws_sns_topic.alerts.arn
  rule      = aws_cloudwatch_event_rule.cross_account_backup_check.name
  #  role_arn  = aws_iam_role.event_sns_publisher.arn
  input = jsonencode(
    {
      job-name        = "cross_account_backup_check"
      log-group       = "backup-cross-account-${terraform.workspace}",
      log-entries     = "cross_account_backup",
      search-timespan = "24 hours",
      bank-holidays   = "true"
    }
  )
}

# Delete inactive users

resource "aws_cloudwatch_event_rule" "delete_inactive_users_check" {
  name                = "delete-inactive-users-check-${terraform.workspace}"
  description         = "Execute the delete inactive users check for ${terraform.workspace}"
  schedule_expression = "cron(11 09 * * ? *)"
  is_enabled          = true
}

resource "aws_lambda_permission" "delete_inactive_users_check" {
  statement_id  = "AllowExecutionFromDeleteInactiveUsersCheck"
  action        = "lambda:InvokeFunction"
  function_name = data.aws_lambda_function.slack_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.delete_inactive_users.arn
}

resource "aws_cloudwatch_event_target" "delete_inactive_users_check" {
  target_id = "delete-inactive-users-check-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.delete_inactive_users_check.name
  #  role_arn  = aws_iam_role.event_sns_publisher.arn
  input = jsonencode(
    {
      job-name        = "delete_inactive_users_check"
      log-group       = terraform.workspace,
      log-entries     = "delete_inactive_users",
      search-timespan = "24 hours",
      bank-holidays   = "true"
    }
  )
}

# Delete zero activity users

resource "aws_cloudwatch_event_rule" "delete_zero_activity_users_check" {
  name                = "delete-zero-activity-users-check-${terraform.workspace}"
  description         = "Execute the delete zero activity users check for ${terraform.workspace}"
  schedule_expression = "cron(12 09 * * ? *)"
  is_enabled          = true
}

resource "aws_lambda_permission" "delete_zero_activity_users_check" {
  statement_id  = "AllowExecutionFromDeleteZeroActivityUsersCheck"
  action        = "lambda:InvokeFunction"
  function_name = data.aws_lambda_function.slack_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.delete_zero_activity_users_check.arn
}

resource "aws_cloudwatch_event_target" "delete_zero_activity_users_check" {
  target_id = "delete-zero-activity-users-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.delete_zero_activity_users_check.name
  #  role_arn  = aws_iam_role.event_sns_publisher.arn
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name        = "delete_zero_activity_users_check"
        log-group       = terraform.workspace,
        log-entries     = "delete_zero_activity_users",
        search-timespan = "24 hours",
        bank-holidays   = "true"
      }
    }
  )
}
