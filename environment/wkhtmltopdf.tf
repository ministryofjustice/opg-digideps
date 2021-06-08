locals {
  wkhtmltopdf_service_fqdn = "wkhtmltopdf.${aws_service_discovery_private_dns_namespace.private.name}"
}

resource "aws_service_discovery_service" "wkhtmltopdf" {
  name = "wkhtmltopdf"

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
}

resource "aws_iam_role" "wkhtmltopdf" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "wkhtmltopdf.${local.environment}"
  tags               = local.default_tags
}

resource "aws_ecs_task_definition" "wkhtmltopdf" {
  family                   = "wkhtmltopdf-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = local.account.cpu_low
  memory                   = local.account.memory_low
  container_definitions    = "[${local.wkhtmltopdf_container}]"
  task_role_arn            = aws_iam_role.wkhtmltopdf.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "wkhtmltopdf" {
  name                    = aws_ecs_task_definition.wkhtmltopdf.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.wkhtmltopdf.arn
  desired_count           = 1
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true

  network_configuration {
    security_groups  = [module.wkhtmltopdf_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.wkhtmltopdf.arn
  }

  depends_on = [aws_service_discovery_service.wkhtmltopdf]

  tags = local.default_tags
}

locals {
  wkhtmltopdf_container = <<EOF
  {
      "cpu": 0,
      "essential": true,
      "image": "${local.images.wkhtmltopdf}",
      "mountPoints": [],
      "name": "wkhtmltopdf",
      "volumesFrom": [],
      "healthCheck": {
        "command": [
          "CMD-SHELL",
          "curl --fail -X POST -H 'Content-Type:application/json' -d '{\"contents\":\"dGVzdA==\"}' -o /dev/null http://localhost:80/ || exit 1"
        ],
        "interval": 30,
        "timeout": 5,
        "retries": 3
      },
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
          "awslogs-region": "eu-west-1",
          "awslogs-stream-prefix": "${aws_iam_role.wkhtmltopdf.name}"
        }
      }
  }

EOF

}
