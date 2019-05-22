locals {
  api_service_fqdn = "api.${aws_service_discovery_private_dns_namespace.private.name}"
}

resource "aws_service_discovery_service" "api" {
  name = "api"

  dns_config {
    namespace_id = "${aws_service_discovery_private_dns_namespace.private.id}"

    dns_records {
      ttl  = 10
      type = "A"
    }

    routing_policy = "MULTIVALUE"
  }

  health_check_custom_config {
    failure_threshold = 1
  }
}

resource "aws_ecs_task_definition" "api" {
  family                   = "api-${terraform.workspace}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.api_container}]"
  task_role_arn            = "${aws_iam_role.api.arn}"
  execution_role_arn       = "${aws_iam_role.execution_role.arn}"
  tags                     = "${local.default_tags}"
}

resource "aws_ecs_service" "api" {
  name                    = "${aws_ecs_task_definition.api.family}"
  cluster                 = "${aws_ecs_cluster.main.id}"
  task_definition         = "${aws_ecs_task_definition.api.arn}"
  desired_count           = "${local.task_count}"
  launch_type             = "FARGATE"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = "${local.default_tags}"

  network_configuration {
    security_groups  = ["${aws_security_group.api_task.id}"]
    subnets          = ["${data.aws_subnet.private.*.id}"]
    assign_public_ip = false
  }

  service_registries {
    registry_arn = "${aws_service_discovery_service.api.arn}"
  }
}

resource "aws_security_group" "api_task" {
  name_prefix = "${aws_ecs_task_definition.api.family}"
  vpc_id      = "${data.aws_vpc.vpc.id}"
  tags        = "${local.default_tags}"

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "api_https_admin_in" {
  type      = "ingress"
  protocol  = "tcp"
  from_port = 443
  to_port   = 443

  security_group_id        = "${aws_security_group.api_task.id}"
  source_security_group_id = "${aws_security_group.admin.id}"
}

resource "aws_security_group_rule" "api_https_front_in" {
  type      = "ingress"
  protocol  = "tcp"
  from_port = 443
  to_port   = 443

  security_group_id        = "${aws_security_group.api_task.id}"
  source_security_group_id = "${aws_security_group.front.id}"
}

resource "aws_security_group_rule" "api_out" {
  type      = "egress"
  protocol  = "-1"
  from_port = 0
  to_port   = 0

  security_group_id = "${aws_security_group.api_task.id}"
  cidr_blocks       = ["0.0.0.0/0"]
}

locals {
  api_container = <<EOF
  {
    "cpu": 0,
    "essential": true,
    "image": "registry.service.opg.digital/opguk/digi-deps-api:${var.OPG_DOCKER_TAG}",
    "repositoryCredentials": {
      "credentialsParameter": "${data.aws_secretsmanager_secret.registry.name}"
    },
    "mountPoints": [],
    "name": "api_app",
    "portMappings": [{
      "containerPort": 443,
      "hostPort": 443,
      "protocol": "tcp"
    }],
    "volumesFrom": [],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.api.name}"
      }
    },
    "secrets": [
      { "name": "API_DATABASE_PASSWORD", "valueFrom": "${data.aws_secretsmanager_secret.database_password.arn}" },
      { "name": "API_SECRET", "valueFrom": "${data.aws_secretsmanager_secret.api_secret.arn}" },
      { "name": "API_SECRETS_ADMIN_KEY", "valueFrom": "${data.aws_secretsmanager_secret.admin_api_client_secret.arn}" },
      { "name": "API_SECRETS_FRONT_KEY", "valueFrom": "${data.aws_secretsmanager_secret.front_api_client_secret.arn}" }
    ],
    "environment": [
      { "name": "API_BEHAT_CONTROLLER_ENABLED", "value": "${local.test_enabled ? "true" : "false" }" },
      { "name": "API_DATABASE_HOSTNAME", "value": "${aws_db_instance.api.address}" },
      { "name": "API_DATABASE_NAME", "value": "${aws_db_instance.api.name}" },
      { "name": "API_DATABASE_PORT", "value": "${aws_db_instance.api.port}" },
      { "name": "API_DATABASE_USERNAME", "value": "digidepsmaster" },
      { "name": "API_REDIS_DSN", "value": "redis://${aws_route53_record.api_redis.fqdn}" },
      { "name": "API_SECRETS_ADMIN_PERMISSIONS", "value": "[ROLE_ADMIN, ROLE_AD, ROLE_CASE_MANAGER]" },
      { "name": "API_SECRETS_FRONT_PERMISSIONS", "value": "[ROLE_LAY_DEPUTY, ROLE_PA, ROLE_PROF, ROLE_PA_ADMIN, ROLE_PA_TEAM_MEMBER]" },
      { "name": "API_SECURITY_ANONYMOUS", "value": "true" },
      { "name": "NGINX_INDEX", "value": "app.php" },
      { "name": "OPG_DOCKER_TAG", "value": "${var.OPG_DOCKER_TAG}" },
      { "name": "OPG_NGINX_CLIENTBODYTIMEOUT", "value": "240s" },
      { "name": "OPG_NGINX_CLIENTMAXBODYSIZE", "value": "10M" },
      { "name": "OPG_NGINX_INDEX", "value": "app.php" },
      { "name": "OPG_NGINX_ROOT", "value": "/app/web" },
      { "name": "OPG_NGINX_SERVER_NAMES", "value": "~.*" },
      { "name": "OPG_NGINX_SSL_FORCE_REDIRECT", "value": "1" },
      { "name": "OPG_PHP_POOL_CHILDREN_MAX", "value": "12" },
      { "name": "OPG_SERVICE", "value": "api" },
      { "name": "OPG_STACKNAME", "value": "${terraform.workspace}" }
    ]
  }
  EOF
}
