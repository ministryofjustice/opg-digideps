module "analyse" {
  source = "./modules/task"
  name   = "psql-analyse"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.psql_analyse}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.restore_security_group.id
}

resource "aws_cloudwatch_event_rule" "psql_analyse" {
  name                = "NightlyPSQLAnalyse-${terraform.workspace}"
  description         = "Execute the Analyse task in ${terraform.workspace}"
  schedule_expression = terraform.workspace == "development" ? "cron(30 08 * * ? *)" : "cron(00 04 * * ? *)"
}

resource "aws_cloudwatch_event_target" "psql_analyse" {
  target_id = "psql-analyse-${terraform.workspace}"
  arn       = aws_ecs_cluster.main.arn
  rule      = aws_cloudwatch_event_rule.psql_analyse.name
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

locals {
  psql_analyse = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.sync,
    command   = ["sh", "./analyse.sh"],
    name      = "psql-analyse",
    logConfiguration = {
      logDriver = "awslogs",
      options = {
        awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
        awslogs-region        = "eu-west-1",
        awslogs-stream-prefix = "psql-analyse"
      }
    },
    environment = [
      {
        name  = "POSTGRES_DATABASE",
        value = local.db.name
      },
      {
        name  = "POSTGRES_PORT",
        value = "5432"
      },
      {
        name  = "POSTGRES_USER",
        value = local.db.username
      },
      {
        name  = "POSTGRES_HOST",
        value = local.db.endpoint
      }
    ],
    secrets = [{
      name      = "POSTGRES_PASSWORD",
      valueFrom = data.aws_secretsmanager_secret.database_password.arn
    }]
  })
}
