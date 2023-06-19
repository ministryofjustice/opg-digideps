module "reset_database" {
  source = "./task"
  name   = "reset-database"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.reset_database_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.reset_database_security_group.id
}

locals {
  reset_database_sg_rules = {
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
  }
}

module "reset_database_security_group" {
  source      = "./security_group"
  description = "Reset Database Service"
  rules       = local.reset_database_sg_rules
  name        = "reset-database"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
}

locals {
  reset_database_container = <<EOF
  {
    "name": "reset-database",
    "image": "${local.images.api}",
    "command": [ "sh", "scripts/reset_db_fixtures.sh" ],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.test.name}"
      }
    },
    "secrets": [
      { "name": "DATABASE_PASSWORD", "valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}" },
      { "name": "SECRET", "valueFrom": "${data.aws_secretsmanager_secret.api_secret.arn}" }
    ],
    "environment": [
      { "name": "DATABASE_HOSTNAME", "value": "${local.db.endpoint}" },
      { "name": "DATABASE_NAME", "value": "${local.db.name}" },
      { "name": "DATABASE_PORT", "value": "${local.db.port}" },
      { "name": "DATABASE_USERNAME", "value": "${local.db.username}" },
      { "name": "FIXTURES_ACCOUNTPASSWORD", "value": "DigidepsPass1234" },
      { "name": "REDIS_DSN", "value": "rediss://${aws_route53_record.api_redis.fqdn}" }
    ]
  }
EOF
}
