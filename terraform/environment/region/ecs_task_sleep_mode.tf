module "sleep_mode" {
  source = "./modules/task"
  name   = "sleep_mode"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.sleep_mode_container}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = aws_iam_role.sleep_mode.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.db_access_task_security_group.id
}

locals {
  sleep_mode_container = jsonencode(
    {
      name    = "sleep-mode",
      image   = local.images.orchestration,
      command = ["./sleep_mode"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "sleep_mode"
        }
      },
      secrets = [],
      environment = concat(local.api_single_db_tasks_base_variables,
        [
          {
            name  = "ENVIRONMENT",
            value = local.environment
          }
      ])
    }
  )
}
