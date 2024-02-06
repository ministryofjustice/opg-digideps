module "restore_from_production" {
  source = "./modules/task"
  name   = "restore-from-production"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.restore_from_production_container}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.db_access_task_security_group.id
}

locals {
  restore_from_production_container = jsonencode(
    {
      name    = "restore",
      image   = local.images.sync,
      command = ["./restore.sh"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "restore"
        }
      },
      secrets = [{
        name      = "POSTGRES_PASSWORD",
        valueFrom = data.aws_secretsmanager_secret.database_password.arn
      }],
      environment = [{
        name  = "S3_BUCKET",
        value = data.aws_s3_bucket.backup.bucket
        },
        {
          name  = "S3_PREFIX",
          value = "production02"
        },
        {
          name  = "POSTGRES_DATABASE",
          value = local.db.name
        },
        {
          name  = "POSTGRES_HOST",
          value = local.db.endpoint
        },
        {
          name  = "POSTGRES_PORT",
          value = tostring(local.db.port)
        },
        {
          name  = "POSTGRES_USER",
          value = local.db.username
        },
        {
          name  = "DROP_PUBLIC",
          value = "yes"
        }
      ]
    }
  )
}
