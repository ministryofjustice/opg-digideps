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
  is_enabled          = var.account.is_production == 1 ? true : false
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
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Delete inactive users check

resource "aws_cloudwatch_event_rule" "delete_inactive_users_check" {
  name                = "check-delete-inactive-users-${terraform.workspace}"
  description         = "Execute the delete inactive users check for ${terraform.workspace}"
  schedule_expression = "cron(11 09 ? * 1 *)"
  is_enabled          = var.account.is_production == 1 ? true : false
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
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Delete zero activity users check

resource "aws_cloudwatch_event_rule" "delete_zero_activity_users_check" {
  name                = "check-delete-zero-activity-users-${terraform.workspace}"
  description         = "Execute the delete zero activity users check for ${terraform.workspace}"
  schedule_expression = "cron(12 09 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
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
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Resubmit re-submittable error documents check

resource "aws_cloudwatch_event_rule" "resubmit_error_documents_check" {
  name                = "check-resync-resubmittable-error-documents-${terraform.workspace}"
  description         = "Execute the resync resubmittable error documents check for ${terraform.workspace}"
  schedule_expression = "cron(13 09 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "resubmit_error_documents_check" {
  target_id = "check-resync-resubmittable-error-documents-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.resubmit_error_documents_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "resubmit_error_documents_check"
        log-group                  = terraform.workspace,
        log-entries                = ["resync_resubmittable_error_documents"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# DB Analyse command check

resource "aws_cloudwatch_event_rule" "db_analyse_command_check" {
  name                = "check-database-analyse-command-${terraform.workspace}"
  description         = "Execute the delete zero activity users check for ${terraform.workspace}"
  schedule_expression = "cron(14 09 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
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
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Check document sync

resource "aws_cloudwatch_event_rule" "sync_documents_check" {
  name                = "check-document-sync-${terraform.workspace}"
  description         = "Execute the document sync check for ${terraform.workspace}"
  schedule_expression = local.sync_service_cron_schedule
  is_enabled          = var.account.is_production == 1 ? true : false
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
        channel-identifier-absent  = "team",
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
  is_enabled          = var.account.is_production == 1 ? true : false
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
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "scheduled-jobs"
      }
    }
  )
}

# Extract Satisfaction Scores

resource "aws_cloudwatch_event_rule" "satisfaction_performance_stats_check" {
  name                = "check-satisfaction-performance-stats-${terraform.workspace}"
  description         = "Extract Satisfaction Scores for ${terraform.workspace}"
  schedule_expression = "cron(0 12 1 * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "satisfaction_performance_stats_check" {
  target_id = "check-satisfaction_performance_stats-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.satisfaction_performance_stats_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "satisfaction_performance_stats_check"
        log-group                  = terraform.workspace,
        log-entries                = ["satisfaction_performance_stats"],
        search-timespan            = local.sync_service_schedule
        bank-holidays              = "true"
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Lay CSV Processing Check

resource "aws_cloudwatch_event_rule" "lay_csv_processing_check" {
  name                = "check-lay-csv-processing-${terraform.workspace}"
  description         = "Execute the Lay CSV user processing check for ${terraform.workspace}"
  schedule_expression = "cron(15 09 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
}


resource "aws_cloudwatch_event_target" "lay_csv_processing_check" {
  target_id = "check-lay-csv-processing-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.lay_csv_processing_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "lay_csv_processing_check"
        log-group                  = terraform.workspace,
        log-entries                = ["lay_csv_processing"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Org CSV Processing Check

resource "aws_cloudwatch_event_rule" "org_csv_processing_check" {
  name                = "check-org-csv-processing-${terraform.workspace}"
  description         = "Execute the Org CSV user processing check for ${terraform.workspace}"
  schedule_expression = "cron(16 09 * * ? *)"
  is_enabled          = var.account.is_production == 1 ? true : false
}


resource "aws_cloudwatch_event_target" "org_csv_processing_check" {
  target_id = "check-org-csv-processing-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.org_csv_processing_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "org_csv_processing_check"
        log-group                  = terraform.workspace,
        log-entries                = ["org_csv_processing"],
        search-timespan            = "24 hours",
        bank-holidays              = "true",
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}

# Delete null user ids in user research

resource "aws_cloudwatch_event_rule" "delete_null_user_research_ids_check" {
  name                = "check-delete-null-user-research-ids-${terraform.workspace}"
  description         = "Delete null user research ids in ${terraform.workspace}"
  schedule_expression = local.sync_service_cron_schedule
  is_enabled          = var.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "delete_null_user_research_ids_check" {
  target_id = "check-delete-null-user-research-ids-${terraform.workspace}"
  arn       = data.aws_lambda_function.slack_lambda.arn
  rule      = aws_cloudwatch_event_rule.delete_null_user_research_ids_check.name
  input = jsonencode(
    {
      scheduled-event-detail = {
        job-name                   = "delete_null_user_research_ids_check"
        log-group                  = terraform.workspace,
        log-entries                = ["delete_null_user_research_ids"],
        search-timespan            = local.sync_service_schedule
        bank-holidays              = "true"
        channel-identifier-absent  = "team",
        channel-identifier-success = "scheduled-jobs",
        channel-identifier-failure = "team"
      }
    }
  )
}
