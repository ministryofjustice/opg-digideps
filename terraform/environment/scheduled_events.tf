# Sirius Lay CSV Ingestion
resource "aws_cloudwatch_event_rule" "delete_zero_activity_users" {
  name                = "delete-zero-activity-users-${local.environment}"
  description         = "Delete zero activity users in ${terraform.workspace}"
  schedule_expression = "cron(0 1 * * ? *)"
  tags                = local.default_tags
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
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:api:process-lay-csv"]
        }
      ]
    }
  )
}

# Sirius Org CSV Ingestion
resource "aws_cloudwatch_event_rule" "delete_zero_activity_users" {
  name                = "delete-zero-activity-users-${local.environment}"
  description         = "Delete zero activity users in ${terraform.workspace}"
  schedule_expression = "cron(0 2 * * ? *)"
  tags                = local.default_tags
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
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
    }
  }
  input = jsonencode(
    {
      "containerOverrides" : [
        {
          "name" : "api_app",
          "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:api:process-org-csv"]
        }
      ]
    }
  )
}

# Delete inactive users

resource "aws_cloudwatch_event_rule" "delete_inactive_users" {
  name                = "delete-inactive-users-${local.environment}"
  description         = "Delete inactive admin users in ${terraform.workspace}"
  schedule_expression = "cron(0 3 ? * 1 *)"
  tags                = local.default_tags
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
      subnets          = data.aws_subnet.private.*.id
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

# Delete zero activity users
resource "aws_cloudwatch_event_rule" "delete_zero_activity_users" {
  name                = "delete-zero-activity-users-${local.environment}"
  description         = "Delete zero activity users in ${terraform.workspace}"
  schedule_expression = "cron(10 3 * * ? *)"
  tags                = local.default_tags
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
      subnets          = data.aws_subnet.private.*.id
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

# Redeploy file scanner

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
      security_groups  = [module.restore_security_group.id]
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
    }
  }
}

# Checklist sync to sirius (in production we have permanent container)

resource "aws_cloudwatch_event_rule" "checklist_sync" {
  count               = local.document_sync_scheduled
  name                = "checklist-sync-${terraform.workspace}"
  schedule_expression = "rate(24 hours)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "checklist_sync" {
  count     = local.document_sync_scheduled
  target_id = "checklist-sync-${terraform.workspace}"
  rule      = aws_cloudwatch_event_rule.checklist_sync[0].name
  arn       = aws_ecs_cluster.main.arn
  role_arn  = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.checklist_sync.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"
    network_configuration {
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
      security_groups  = [module.checklist_sync_service_security_group.id]
    }
  }
}

# Document sync to sirius (in production we have permanent container)

resource "aws_cloudwatch_event_rule" "document_sync" {
  count               = local.document_sync_scheduled
  name                = "document-sync-${terraform.workspace}"
  schedule_expression = "rate(24 hours)"
  tags                = local.default_tags
}

resource "aws_cloudwatch_event_target" "document_sync" {
  count     = local.document_sync_scheduled
  target_id = "document-sync-${terraform.workspace}"
  rule      = aws_cloudwatch_event_rule.document_sync[0].name
  arn       = aws_ecs_cluster.main.arn
  role_arn  = aws_iam_role.events_task_runner.arn

  ecs_target {
    task_count          = 1
    task_definition_arn = aws_ecs_task_definition.document_sync.arn
    launch_type         = "FARGATE"
    platform_version    = "1.4.0"
    network_configuration {
      subnets          = data.aws_subnet.private.*.id
      assign_public_ip = false
      security_groups  = [module.document_sync_service_security_group.id]
    }
  }
}
