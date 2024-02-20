module "analyse" {
  source = "./modules/task"
  name   = "database-analyse-command"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.psql_analyse}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.db_access_task_security_group.id
}

locals {
  psql_analyse = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.sync,
    command   = ["sh", "./analyse-database.sh"],
    name      = "database-analyse-command",
    logConfiguration = {
      logDriver = "awslogs",
      options = {
        awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
        awslogs-region        = "eu-west-1",
        awslogs-stream-prefix = "database-analyse-command"
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
