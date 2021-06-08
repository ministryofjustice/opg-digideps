locals {
  api_service_fqdn = "api.${aws_service_discovery_private_dns_namespace.private.name}"
}

resource "aws_service_discovery_service" "api" {
  name = "api"

  dns_config {
    namespace_id = aws_service_discovery_private_dns_namespace.private.id

    dns_records {
      ttl  = 10
      type = "A"
    }

    routing_policy = "MULTIVALUE"
  }

  health_check_custom_config {
    failure_threshold = 1
  }

  tags = local.default_tags

  depends_on = [aws_service_discovery_private_dns_namespace.private]
}

resource "aws_ecs_task_definition" "api" {
  family                   = "api-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.api_container}]"
  task_role_arn            = aws_iam_role.api.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "api" {
  name                    = aws_ecs_task_definition.api.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.api.arn
  desired_count           = local.account.task_count
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [module.api_service_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.api.arn
  }

  depends_on = [aws_service_discovery_service.api]
}

locals {
  api_container = <<EOF
  {
    "cpu": 0,
    "essential": true,
    "image": "${local.images.api}",
    "mountPoints": [],
    "name": "api_app",
    "portMappings": [{
      "containerPort": 443,
      "hostPort": 443,
      "protocol": "tcp"
    }],
    "volumesFrom": [],
    "stopTimeout": 60,
    "healthCheck": {
      "command": [
        "CMD-SHELL",
        "curl -f -k https://localhost:443/manage/elb || exit 1"
      ],
      "interval": 30,
      "timeout": 10,
      "retries": 3
    },
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.api.name}"
      }
    },
    "secrets": [
      { "name": "DATABASE_PASSWORD", "valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}" },
      { "name": "SECRET", "valueFrom": "${data.aws_secretsmanager_secret.api_secret.arn}" },
      { "name": "SECRETS_ADMIN_KEY", "valueFrom": "${data.aws_secretsmanager_secret.admin_api_client_secret.arn}" },
      { "name": "SECRETS_FRONT_KEY", "valueFrom": "${data.aws_secretsmanager_secret.front_api_client_secret.arn}" }
    ],
    "environment": [
      { "name": "AUDIT_LOG_GROUP_NAME", "value": "audit-${local.environment}" },
      { "name": "DATABASE_HOSTNAME", "value": "${local.db.endpoint}" },
      { "name": "DATABASE_NAME", "value": "${local.db.name}" },
      { "name": "DATABASE_PORT", "value": "${local.db.port}" },
      { "name": "DATABASE_USERNAME", "value": "${local.db.username}" },
      { "name": "FIXTURES_ACCOUNTPASSWORD", "value": "DigidepsPass1234" },
      { "name": "NGINX_APP_NAME", "value": "api" },
      { "name": "OPG_DOCKER_TAG", "value": "${var.OPG_DOCKER_TAG}" },
      { "name": "REDIS_DSN", "value": "redis://${aws_route53_record.api_redis.fqdn}" }
    ]
  }

EOF

}
