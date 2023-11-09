module "integration_test_v2" {
  source = "./modules/task"
  name   = "integration-test-v2"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.integration_test_v2_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.integration_test_v2_security_group.id
  cpu                   = 4096
  memory                = 8192
  override              = ["sh", "./tests/Behat/run-tests-parallel.sh"]
  service_name          = "integration-test-v2"
}

locals {
  integration_test_v2_sg_rules = {
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
    front_http = {
      port        = 80
      protocol    = "tcp"
      type        = "egress"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "integration_test_v2_security_group" {
  source      = "./modules/security_group"
  description = "Integration Tests V2 Service"
  rules       = local.integration_test_v2_sg_rules
  name        = "integration-test-v2"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

locals {
  integration_test_v2_container = jsonencode(
    {
      name  = "integration-test-v2",
      image = local.images.api,
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = aws_iam_role.test.name
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
      environment = [
        {
          name  = "PGHOST",
          value = local.db.endpoint
        },
        {
          name  = "PGDATABASE",
          value = local.db.name
        },
        {
          name  = "PGUSER",
          value = local.db.username
        },
        {
          name  = "ADMIN_HOST",
          value = "https://${aws_route53_record.admin.fqdn}"
        },
        {
          name  = "NONADMIN_HOST",
          value = "https://${aws_route53_record.front.fqdn}"
        },
        {
          name  = "AUDIT_LOG_GROUP_NAME",
          value = "audit-${local.environment}"
        },
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
          name  = "DATABASE_SSL",
          value = "verify-full"
        },
        {
          name  = "FEATURE_FLAG_PREFIX",
          value = local.feature_flag_prefix
        },
        {
          name  = "FIXTURES_ACCOUNTPASSWORD",
          value = "DigidepsPass1234"
        },
        {
          name  = "NGINX_APP_NAME",
          value = "api"
        },
        {
          name  = "OPG_DOCKER_TAG",
          value = var.OPG_DOCKER_TAG
        },
        {
          name  = "PARAMETER_PREFIX",
          value = local.parameter_prefix
        },
        {
          name  = "REDIS_DSN",
          value = "redis://${aws_route53_record.api_redis.fqdn}"
        },
        {
          name  = "WORKSPACE",
          value = local.environment
        }
      ]
    }
  )
}
