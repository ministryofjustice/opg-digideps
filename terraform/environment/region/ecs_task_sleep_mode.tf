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
  security_group_id     = module.sleep_mode_security_group.id
}

locals {
  sleep_mode_container = jsonencode(
    {
      name    = "sleep-mode",
      image   = local.images.orchestration,
      command = ["./environment_status"],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "sleep_mode"
        }
      },
      secrets = [],
      environment = [
        {
          name  = "ENVIRONMENT",
          value = local.environment
        }
      ]
    }
  )
}

locals {
  sleep_mode_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    outbound_rds = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "sleep_mode_security_group" {
  source      = "./modules/security_group"
  name        = "sleep-mode"
  description = "Sleep Mode SG Rules"
  rules       = local.sleep_mode_sg_rules
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
