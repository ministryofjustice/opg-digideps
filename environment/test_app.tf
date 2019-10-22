resource "aws_ecs_service" "test" {
  count                   = local.account.test_enabled ? 1 : 0
  name                    = "test-${local.environment}"
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.reset_database[0].arn
  desired_count           = 0
  launch_type             = "FARGATE"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = local.default_tags

  network_configuration {
    security_groups = [
      aws_security_group.front.id,
      aws_security_group.api_task.id,
    ]

    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }
}

resource "aws_ecs_task_definition" "test_front" {
  count                    = local.account.test_enabled ? 1 : 0
  family                   = "test-front-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.test_front_container}]"
  task_role_arn            = aws_iam_role.test.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_task_definition" "test_api" {
  count                    = local.account.test_enabled ? 1 : 0
  family                   = "test-api-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.test_api_container}]"
  task_role_arn            = aws_iam_role.test.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_task_definition" "reset_database" {
  count                    = local.account.test_enabled ? 1 : 0
  family                   = "reset-database-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.reset_database_container}]"
  task_role_arn            = aws_iam_role.test.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

locals {
  reset_database_container = <<EOF
  {
    "name": "reset_database",
    "image": "${local.images.api}",
    "command": [ "sh", "scripts/resetdb.sh" ],
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
      { "name": "BEHAT_CONTROLLER_ENABLED", "value": "true" },
      { "name": "DATABASE_HOSTNAME", "value": "${aws_db_instance.api.address}" },
      { "name": "DATABASE_NAME", "value": "${aws_db_instance.api.name}" },
      { "name": "DATABASE_PORT", "value": "${aws_db_instance.api.port}" },
      { "name": "DATABASE_USERNAME", "value": "digidepsmaster" },
      { "name": "FIXTURES_ACCOUNTPASSWORD", "value": "Abcd1234" },
      { "name": "REDIS_DSN", "value": "redis://${aws_route53_record.api_redis.fqdn}" }
    ]
  }

EOF


  test_api_container = <<EOF
  {
    "name": "test_api",
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
      { "name": "BEHAT_CONTROLLER_ENABLED", "value": "true" },
      { "name": "DATABASE_HOSTNAME", "value": "${aws_db_instance.api.address}" },
      { "name": "DATABASE_NAME", "value": "${aws_db_instance.api.name}" },
      { "name": "DATABASE_PORT", "value": "${aws_db_instance.api.port}" },
      { "name": "DATABASE_USERNAME", "value": "digidepsmaster" },
      { "name": "FIXTURES_ACCOUNTPASSWORD", "value": "Abcd1234" },
      { "name": "REDIS_DSN", "value": "redis://${aws_route53_record.api_redis.fqdn}" },
      { "name": "SECRETS_ADMIN_PERMISSIONS", "value": "[ROLE_ADMIN, ROLE_AD, ROLE_CASE_MANAGER]" },
      { "name": "SECRETS_FRONT_PERMISSIONS", "value": "[ROLE_LAY_DEPUTY, ROLE_PA, ROLE_PROF, ROLE_PA_ADMIN, ROLE_PA_TEAM_MEMBER]" }
    ]
  }

EOF


  test_front_container = <<EOF
  {
    "name": "test_front",
    "image": "${local.images.client}",
    "command": [ "sh", "scripts/clienttest.sh" ],
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
      { "name": "FRONTEND_API_CLIENT_SECRET", "valueFrom": "${data.aws_secretsmanager_secret.front_api_client_secret.arn}" },
      { "name": "FRONTEND_SECRET", "valueFrom": "${data.aws_secretsmanager_secret.front_frontend_secret.arn}" }
    ],
    "environment": [
      { "name": "DATABASE_HOSTNAME", "value": "${aws_db_instance.api.address}" },
      { "name": "DATABASE_NAME", "value": "${aws_db_instance.api.name}" },
      { "name": "DATABASE_PORT", "value": "${aws_db_instance.api.port}" },
      { "name": "DATABASE_USERNAME", "value": "digidepsmaster" },
      { "name": "FRONTEND_ADMIN_HOST", "value": "https://${aws_route53_record.admin.fqdn}" },
      { "name": "FRONTEND_API_URL", "value": "https://${local.api_service_fqdn}" },
      { "name": "FRONTEND_BEHAT_CONTROLLER_ENABLED", "value": "true" },
      { "name": "FRONTEND_EMAIL_DOMAIN", "value": "${local.domain}" },
      { "name": "FRONTEND_EMAIL_SEND_INTERNAL", "value": "${local.account.is_production == 1 ? "true" : "false"}" },
      { "name": "FRONTEND_FILESCANNER_SSLVERIFY", "value": "False" },
      { "name": "FRONTEND_FILESCANNER_URL", "value": "https://${local.scan_service_fqdn}:8443" },
      { "name": "FRONTEND_GA_DEFAULT", "value": "${local.account.ga_default}" },
      { "name": "FRONTEND_GA_GDS", "value": "${local.account.ga_gds}" },
      { "name": "FRONTEND_NONADMIN_HOST", "value": "https://${aws_route53_record.front.fqdn}" },
      { "name": "FRONTEND_ROLE", "value": "front" },
      { "name": "FRONTEND_S3_BUCKETNAME", "value": "pa-uploads-${local.environment}" },
      { "name": "FRONTEND_SESSION_REDIS_DSN", "value": "redis://${aws_route53_record.front_redis.fqdn}" },
      { "name": "FRONTEND_SMTP_DEFAULT_PASSWORD", "value": "${aws_iam_access_key.ses.ses_smtp_password}" },
      { "name": "FRONTEND_SMTP_DEFAULT_USER", "value": "${aws_iam_access_key.ses.id}" },
      { "name": "OPG_DOCKER_TAG", "value": "${var.OPG_DOCKER_TAG}" },
      { "name": "WKHTMLTOPDF_ADDRESS", "value": "http://${local.wkhtmltopdf_service_fqdn}" }
    ]
  }

EOF

}
