module "end_to_end_tests" {
  source = "./modules/task"
  name   = "end_to_end_tests"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.end_to_end_tests_container}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role_db.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = aws_iam_role.end_to_end_tests.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.end_to_end_tests_security_group.id
  cpu                   = 4096
  memory                = 8192
  override              = ["sh", "./tests/Behat/run-tests-parallel.sh"]
  service_name          = "end_to_end_tests"
}

locals {
  end_to_end_tests_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
    front = {
      port        = 443
      protocol    = "tcp"
      type        = "egress"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    #trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
    front_http = {
      port        = 80
      protocol    = "tcp"
      type        = "egress"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

#trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
module "end_to_end_tests_security_group" {
  source      = "./modules/security_group"
  description = "End to End Tests V2 Service"
  rules       = local.end_to_end_tests_sg_rules
  name        = "end-to-end-tests"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

locals {
  end_to_end_tests_container = jsonencode(
    {
      name  = "end-to-end-tests",
      image = local.images.api,
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "end-to-end-tests"
        }
      },
      secrets = [
        {
          name      = "PGPASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        },
        {
          name      = "SECRET",
          valueFrom = data.aws_secretsmanager_secret.front_frontend_secret.arn
        },
        {
          name      = "DATABASE_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        },
        {
          name      = "SECRETS_ADMIN_KEY",
          valueFrom = data.aws_secretsmanager_secret.admin_api_client_secret.arn
        },
        {
          name      = "SECRETS_FRONT_KEY",
          valueFrom = data.aws_secretsmanager_secret.front_api_client_secret.arn
        }
      ],
      environment = concat(local.api_base_variables, local.api_service_variables, local.api_integration_test_variables)
    }
  )
}
