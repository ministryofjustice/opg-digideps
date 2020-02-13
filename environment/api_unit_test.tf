module "api_unit_test" {
  source = "./task"
  name   = "api-unit-test"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.api_unit_test_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  memory                = 1024
  security_group_id     = module.api_unit_test_security_group.id
}

locals {
  api_unit_test_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }

    cache = {
      port        = 6379
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_cache_security_group.id
    }
  }
}

module "api_unit_test_security_group" {
  source = "./security_group"
  rules  = local.api_unit_test_sg_rules
  name   = "api-unit-test"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  api_unit_test_container = <<EOF
  {
    "name": "api-unit-test",
    "image": "${local.images.api}",
    "command": [ "sh", "scripts/apiunittest.sh" ],
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
      { "name": "SECRET", "valueFrom": "${data.aws_secretsmanager_secret.api_secret.arn}" },
      { "name": "SECRETS_ADMIN_KEY", "valueFrom": "${data.aws_secretsmanager_secret.admin_api_client_secret.arn}" },
      { "name": "SECRETS_FRONT_KEY", "valueFrom": "${data.aws_secretsmanager_secret.front_api_client_secret.arn}" }
    ],
    "environment": [
      { "name": "DATABASE_HOSTNAME", "value": "${aws_db_instance.api.address}" },
      { "name": "DATABASE_NAME", "value": "${aws_db_instance.api.name}" },
      { "name": "DATABASE_PORT", "value": "${aws_db_instance.api.port}" },
      { "name": "DATABASE_USERNAME", "value": "digidepsmaster" },
      { "name": "FIXTURES_ACCOUNTPASSWORD", "value": "Abcd1234" },
      { "name": "REDIS_DSN", "value": "redis://${aws_route53_record.api_redis.fqdn}" }
    ]
  }
EOF
}
