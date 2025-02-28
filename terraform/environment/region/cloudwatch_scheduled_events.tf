# Sirius CourtOrder CSV Ingestion

resource "aws_cloudwatch_event_rule" "csv_automation_court_order_processing" {
  name                = "csv-automation-court-order-processing-${local.environment}"
  description         = "Process Sirus Court Orders CSV for all Users ${terraform.workspace}"
  schedule_expression = "cron(59 1 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "csv_automation_court_order_processing" {
  rule     = aws_cloudwatch_event_rule.csv_automation_court_order_processing.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api_high_memory.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:api:process-court-orders-csv", "--env=prod", "--no-debug", local.court_order_report_csv_file]
        }
      ]
    }
  )
}

# Sirius Lay CSV Ingestion

resource "aws_cloudwatch_event_rule" "csv_automation_lay_processing" {
  name                = "csv-automation-lay-processing-${local.environment}"
  description         = "Process Sirus Lay CSV for Lay Users ${terraform.workspace}"
  schedule_expression = "cron(0 2 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "csv_automation_lay_processing" {
  rule     = aws_cloudwatch_event_rule.csv_automation_lay_processing.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api_high_memory.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:api:process-lay-csv", "--multiclient-apply-db-changes=false", "--env=prod", "--no-debug", local.lay_report_csv_file]
        }
      ]
    }
  )
}

# Sirius Org CSV Ingestion

resource "aws_cloudwatch_event_rule" "csv_automation_org_processing" {
  name                = "csv-automation-org-processing-${local.environment}"
  description         = "Process Sirus Org CSV for Org Users  ${terraform.workspace}"
  schedule_expression = "cron(30 2 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "csv_automation_org_processing" {
  rule     = aws_cloudwatch_event_rule.csv_automation_org_processing.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api_high_memory.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:api:process-org-csv", "--env=prod", "--no-debug", local.pa_pro_report_csv_filename]
        }
      ]
    }
  )
}

# Delete inactive users

resource "aws_cloudwatch_event_rule" "delete_inactive_users" {
  name                = "delete-inactive-users-${local.environment}"
  description         = "Delete inactive admin users in ${terraform.workspace}"
  schedule_expression = "cron(0 6 ? * 1 *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "delete_inactive_users" {
  rule     = aws_cloudwatch_event_rule.delete_inactive_users.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }

  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:delete-inactive-users"]
        }
      ]
    }
  )
}

# Delete zero activity users - Temp disabled due to change in data structure linking additional tables.

resource "aws_cloudwatch_event_rule" "delete_zero_activity_users" {
  name                = "delete-zero-activity-users-${local.environment}"
  description         = "Delete zero activity users in ${terraform.workspace}"
  schedule_expression = "cron(20 6 * * ? *)"
  tags                = var.default_tags
  is_enabled          = false
}

resource "aws_cloudwatch_event_target" "delete_zero_activity_users" {
  rule     = aws_cloudwatch_event_rule.delete_zero_activity_users.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:delete-zero-activity-users"]
        }
      ]
    }
  )
}

# Resubmit re-submittable error documents

resource "aws_cloudwatch_event_rule" "resubmit_error_documents" {
  name                = "resync-resubmittable-error-documents-${local.environment}"
  description         = "Resync resubmittable error documents in ${terraform.workspace}"
  schedule_expression = "cron(00 08 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "resubmit_error_documents" {
  rule     = aws_cloudwatch_event_rule.resubmit_error_documents.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:resync-resubmittable-error-documents"]
        }
      ]
    }
  )
}

# Resubmit re-submittable error checklists

resource "aws_cloudwatch_event_rule" "resubmit_error_checklists" {
  name                = "resync-resubmittable-error-checklists-${local.environment}"
  description         = "Resync resubmittable error checklists in ${terraform.workspace}"
  schedule_expression = "cron(05 08 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "resubmit_error_checklists" {
  rule     = aws_cloudwatch_event_rule.resubmit_error_checklists.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.api.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:resync-resubmittable-error-checklists"]
        }
      ]
    }
  )
}

# Redeploy file scanner

data "aws_lambda_function" "redeployer_lambda" {
  function_name = "redeployer"
}

resource "aws_cloudwatch_event_rule" "redeploy_file_scanner" {
  name                = "redeploy-file-scanner-${local.environment}"
  description         = "Redeploy the file scanner to use latest virus definitions"
  schedule_expression = "cron(0 1 * * ? *)"
  tags                = var.default_tags
}

resource "aws_cloudwatch_event_target" "redeploy_file_scanner" {
  rule = aws_cloudwatch_event_rule.redeploy_file_scanner.name
  arn  = data.aws_lambda_function.redeployer_lambda.arn
  input = jsonencode(
    {
      cluster = aws_ecs_cluster.main.name,
      service = aws_ecs_service.scan.name
    }
  )
}

resource "aws_lambda_permission" "allow_cloudwatch_call_lambda" {
  statement_id  = "AllowExecutionFrom-${aws_cloudwatch_event_rule.redeploy_file_scanner.name}"
  action        = "lambda:InvokeFunction"
  function_name = data.aws_lambda_function.redeployer_lambda.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.redeploy_file_scanner.arn
}

# Run DB Analyse command

resource "aws_cloudwatch_event_rule" "db_analyse_command" {
  name                = "database-analyse-command-${terraform.workspace}"
  description         = "Execute the Analyse Database task in ${terraform.workspace}"
  schedule_expression = terraform.workspace == "development" ? "cron(30 08 * * ? *)" : "cron(00 04 * * ? *)"
}

resource "aws_cloudwatch_event_target" "db_analyse_command" {
  target_id = "database-analyse-command-${terraform.workspace}"
  arn       = aws_ecs_cluster.main.arn
  rule      = aws_cloudwatch_event_rule.db_analyse_command.name
  role_arn  = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = module.analyse.task_definition_arn
    launch_type         = "FARGATE"

    network_configuration {
      security_groups  = [module.db_access_task_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
}

# Extract Satisfaction Scores

resource "aws_cloudwatch_event_rule" "satisfaction_performance_stats" {
  name                = "satisfaction-performance-stats-${local.environment}"
  description         = "Extract Satisfaction Scores in ${terraform.workspace}"
  schedule_expression = "cron(0 10 1 * ? *)"
  tags                = var.default_tags
  is_enabled          = var.account.is_production == 1 ? true : false
}

resource "aws_cloudwatch_event_target" "satisfaction_performance_stats" {
  rule     = aws_cloudwatch_event_rule.satisfaction_performance_stats.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = module.performance_data.task_definition_arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.api_service_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "performance-data",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:satisfaction-performance-stats"]
        }
      ]
    }
  )
}

# Sleep mode - Turn on environment

resource "aws_cloudwatch_event_rule" "sleep_mode_on" {
  name                = "sleep-mode-on-${local.environment}"
  description         = "Sleep mode - turn on environment ${terraform.workspace}"
  schedule_expression = "cron(0 07,23 * * ? *)"
  tags                = var.default_tags
  is_enabled          = var.account.sleep_mode_enabled ? true : false
}

resource "aws_cloudwatch_event_target" "sleep_mode_on" {
  rule     = aws_cloudwatch_event_rule.sleep_mode_on.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = module.sleep_mode.task_definition_arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.sleep_mode_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "sleep-mode",
          "command" : ["./environment_status", "-action=ON"]
        }
      ]
    }
  )
}

# Sleep mode - Turn off environment

resource "aws_cloudwatch_event_rule" "sleep_mode_off" {
  name                = "sleep-mode-off-${local.environment}"
  description         = "Sleep mode - turn off environment ${terraform.workspace}"
  schedule_expression = "cron(15 02,20 * * ? *)"
  tags                = var.default_tags
  is_enabled          = var.account.sleep_mode_enabled ? true : false
}

resource "aws_cloudwatch_event_target" "sleep_mode_off" {
  rule     = aws_cloudwatch_event_rule.sleep_mode_off.name
  arn      = aws_ecs_cluster.main.arn
  role_arn = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = module.sleep_mode.task_definition_arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"

    network_configuration {
      security_groups  = [module.sleep_mode_security_group.id]
      subnets          = data.aws_subnet.private[*].id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "sleep-mode",
          "command" : ["./environment_status", "-action=OFF"]
        }
      ]
    }
  )
}

# Block malicious IPs on the WAF
data "aws_lambda_function" "block_ips_lambda" {
  function_name = "block-ips"
}

resource "aws_cloudwatch_event_rule" "block_ips" {
  name                = "block-ips-${terraform.workspace}"
  description         = "Execute the blocking of malicious IPs for ${terraform.workspace}"
  schedule_expression = "rate(5 minutes)"
  is_enabled          = var.account.waf_ip_blocking_enabled
}

resource "aws_cloudwatch_event_target" "block_ips" {
  target_id = "block-ips-${terraform.workspace}"
  arn       = data.aws_lambda_function.block_ips_lambda.arn
  rule      = aws_cloudwatch_event_rule.block_ips.name
}
