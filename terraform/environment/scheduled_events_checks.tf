data "aws_lambda_function" "slack_lambda" {
  function_name = "slack-notifier"
}

locals {
  sync_service_schedule      = "24 hours"
  sync_service_cron_schedule = "cron(00 05 ? * * *)"
}

# Cross account DR backup check

resource "aws_cloudwatch_event_rule" "cross_account_backup_check" {
  name                = "check-backup-cross-account-${terraform.workspace}"
  description         = "Execute the cross account DR backup check for ${terraform.workspace}"
  schedule_expression = "cron(10 09 * * ? *)"
  is_enabled          = local.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "cross_account_backup_check" {
  target_id = "check-backup-cross-account-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.cross_account_backup_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "cross_account_backup_check"
        log-group                  = "backup-cross-account-${terraform.workspace}",
        log-entries                = ["cross_account_backup"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}

# Delete inactive users check

resource "aws_cloudwatch_event_rule" "delete_inactive_users_check" {
  name                = "check-delete-inactive-users-${terraform.workspace}"
  description         = "Execute the delete inactive users check for ${terraform.workspace}"
  schedule_expression = "cron(0 10 ? * 1 *)"
  is_enabled          = local.account.is_production == 1 ? true : false
}


resource "aws_cloudwatch_event_target" "delete_inactive_users_check" {
  target_id = "check-delete-inactive-users-check-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.delete_inactive_users_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "delete_inactive_users_check"
        log-group                  = terraform.workspace,
        log-entries                = ["delete_inactive_users"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}

# Delete zero activity users check

resource "aws_cloudwatch_event_rule" "delete_zero_activity_users_check" {
  name                = "check-delete-zero-activity-users-${terraform.workspace}"
  description         = "Execute the delete zero activity users check for ${terraform.workspace}"
  schedule_expression = "cron(12 09 * * ? *)"
  is_enabled          = local.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "delete_zero_activity_users_check" {
  target_id = "check-delete-zero-activity-users-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.delete_zero_activity_users_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "delete_zero_activity_users_check"
        log-group                  = terraform.workspace,
        log-entries                = ["delete_zero_activity_users"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}

# DB Analyse command check

resource "aws_cloudwatch_event_rule" "db_analyse_command_check" {
  name                = "check-database-analyse-command-${terraform.workspace}"
  description         = "Execute the delete zero activity users check for ${terraform.workspace}"
  schedule_expression = "cron(13 09 * * ? *)"
  is_enabled          = local.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "db_analyse_command_check" {
  target_id = "check-database-analyse-command-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.db_analyse_command_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "db_analyse_command_check"
        log-group                  = terraform.workspace,
        log-entries                = ["analyze_database"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}

# Check document sync

resource "aws_cloudwatch_event_rule" "sync_documents_check" {
  name                = "check-document-sync-${terraform.workspace}"
  description         = "Execute the document sync check for ${terraform.workspace}"
  schedule_expression = local.sync_service_cron_schedule
  is_enabled          = local.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "sync_documents_check" {
  target_id = "check-document-sync-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.sync_documents_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "sync_documents_check"
        log-group                  = terraform.workspace,
        log-entries                = ["sync_documents_to_sirius"],
        search-timespan            = local.sync_service_schedule
        bank-holidays              = "true"
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}

# Check checklist sync

resource "aws_cloudwatch_event_rule" "sync_checklists_check" {
  name                = "check-checklist-sync-${terraform.workspace}"
  description         = "Execute the checklist sync check for ${terraform.workspace}"
  schedule_expression = local.sync_service_cron_schedule
  is_enabled          = local.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "sync_checklists_check" {
  target_id = "check-checklist-sync-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.sync_checklists_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "sync_checklists_check"
        log-group                  = terraform.workspace,
        log-entries                = ["sync_checklists_to_sirius"],
        search-timespan            = local.sync_service_schedule
        bank-holidays              = "true"
        channel-identifier-absent  = "scheduled-jobs",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}
