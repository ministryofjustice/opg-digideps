locals {
  scan_service_fqdn = "scan.${aws_service_discovery_private_dns_namespace.private.name}"
}

resource "aws_service_discovery_service" "scan" {
  name = "scan"

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

  force_destroy = local.account.deletion_protection ? false : true
}

resource "aws_iam_role" "scan" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "scan.${local.environment}"
  tags               = local.default_tags
}

resource "aws_ecs_task_definition" "scan" {
  family                   = "scan-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 1024
  memory                   = local.account.memory_high
  container_definitions    = "[${local.file_scanner_rest_container}]"
  task_role_arn            = aws_iam_role.scan.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "scan" {
  name                    = aws_ecs_task_definition.scan.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.scan.arn
  desired_count           = local.account.scan_count
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true

  network_configuration {
    security_groups  = [module.scan_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.scan.arn
  }

  depends_on = [aws_service_discovery_service.scan]

  tags = local.default_tags
}

locals {
  file_scanner_rest_container = <<EOF
  {
      "name": "rest",
      "essential": true,
      "portMappings": [{
        "containerPort": 8080,
        "hostPort": 8080,
        "protocol": "tcp"
      }],
      "cpu": 0,
      "image": "${local.images.file-scanner}",
      "mountPoints": [],
      "volumesFrom": [],
      "healthCheck": {
        "command": [
          "CMD-SHELL",
          "wget --no-verbose --tries=1 --spider http://localhost:8080/health || exit 1"
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
          "awslogs-stream-prefix": "${aws_iam_role.scan.name}"
        }
      }
  }
EOF
}
