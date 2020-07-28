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
  memory                   = 2048
  container_definitions    = "[${local.file_scanner_rest_container},${local.file_scanner_server_container}]"
  task_role_arn            = aws_iam_role.scan.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "scan" {
  name                    = aws_ecs_task_definition.scan.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.scan.arn
  desired_count           = 2
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"

  network_configuration {
    security_groups  = [module.scan_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.scan.arn
  }

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
      "image": "lokori/clamav-rest",
      "mountPoints": [],
      "volumesFrom": [],
      "dependsOn": [
        {
          "containerName": "server",
          "condition": "HEALTHY"
        }
      ],
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
          "awslogs-region": "eu-west-1",
          "awslogs-stream-prefix": "${aws_iam_role.scan.name}"
        }
      },
      "environment": [
        { "name": "CLAMD_HOST", "value": "127.0.0.1" }
      ]
  }

EOF

  file_scanner_server_container = <<EOF
  {
      "name": "server",
      "essential": true,
      "cpu": 0,
      "image": "mkodockx/docker-clamav",
      "mountPoints": [],
      "volumesFrom": [],
      "healthCheck": {
        "command": [
          "CMD-SHELL",
          "wget --quiet --tries=1 --spider http://localhost:3310/"
        ],
        "interval": 30,
        "retries": 5,
        "timeout": 5
      },
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
          "awslogs-region": "eu-west-1",
          "awslogs-stream-prefix": "${aws_iam_role.scan.name}"
        }
      },
      "environment": [
        { "name": "CLAMD_CONF_SelfCheck", "value": "0" },
        { "name": "FRESHCLAM_CONF_NotifyClamd", "value": "no" }
      ]
  }

EOF

}
