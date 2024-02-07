module "performance_data" {
  source = "./modules/task"
  name   = "performance-data"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.performance_data_container}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = aws_iam_role.performance_data.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.db_access_task_security_group.id
}

locals {
  performance_data_container = jsonencode(
    {
      name  = "performance-data",
      image = local.images.api,
      "command" : ["sh", "scripts/task_run_console_command.sh", "digideps:satisfaction-performance-stats"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "performance-data"
        }
      },
      secrets = [
        {
          name      = "DATABASE_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        },
        {
          name      = "SECRET",
          valueFrom = data.aws_secretsmanager_secret.api_secret.arn
        }
      ],
      environment = [
        {
          name  = "DATABASE_HOSTNAME",
          value = local.db.endpoint
        },
        {
          name  = "DATABASE_NAME",
          value = local.db.name
        },
        {
          name  = "DATABASE_PORT",
          value = tostring(local.db.port)
        },
        {
          name  = "DATABASE_USERNAME",
          value = local.db.username
        },
        {
          name  = "FIXTURES_ACCOUNTPASSWORD",
          value = "DigidepsPass1234"
        },
        {
          name  = "REDIS_DSN",
          value = "redis://${aws_route53_record.api_redis.fqdn}"
        }
      ]
    }
  )
}
