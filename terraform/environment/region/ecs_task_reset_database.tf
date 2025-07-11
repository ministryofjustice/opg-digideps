module "reset_database" {
  source = "./modules/task"
  name   = "reset-database"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.reset_database_container}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role_db.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = aws_iam_role.task_runner.arn
  architecture          = "ARM64"
  os                    = "LINUX"
  security_group_id     = module.db_access_task_security_group.id
}

locals {
  reset_database_container = jsonencode(
    {
      name    = "reset-database",
      image   = local.images.test,
      command = ["sh", "scripts/reset_db_fixtures.sh"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "reset-database"
        }
      },
      secrets = [
        {
          name      = "DATABASE_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        },
        {
          name      = "CUSTOM_SQL_DATABASE_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.custom_sql_db_password.arn
        },
        {
          name      = "SECRET",
          valueFrom = data.aws_secretsmanager_secret.api_secret.arn
        }
      ],
      environment = concat(local.api_base_variables, local.api_service_variables, local.api_testing_app_variables)
    }
  )
}
