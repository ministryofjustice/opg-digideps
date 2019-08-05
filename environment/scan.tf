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
  name               = "scan.${terraform.workspace}"
  tags               = local.default_tags
}

resource "aws_ecs_task_definition" "scan" {
  family                   = "scan-${terraform.workspace}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 1024
  memory                   = 2048
  container_definitions    = "[${local.file_scanner_api_container},${local.file_scanner_worker_container},${local.file_scanner_redis_container}]"
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
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"

  network_configuration {
    security_groups  = [aws_security_group.scan.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.scan.arn
  }

  tags = local.default_tags
}

resource "aws_security_group" "scan" {
  name_prefix = aws_ecs_task_definition.scan.family
  vpc_id      = data.aws_vpc.vpc.id

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(
    local.default_tags,
    {
      "Name" = "scan"
    },
  )
}

resource "aws_security_group_rule" "scan_https_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 8443
  to_port                  = 8443
  security_group_id        = aws_security_group.scan.id
  source_security_group_id = aws_security_group.front.id
}

resource "aws_security_group_rule" "scan_out" {
  type              = "egress"
  protocol          = "-1"
  from_port         = 0
  to_port           = 0
  security_group_id = aws_security_group.scan.id
  cidr_blocks       = ["0.0.0.0/0"]
}

locals {
  file_scanner_api_container = <<EOF
  {
      "name": "api",
      "essential": true,
      "portMappings": [{
        "containerPort": 8443,
        "hostPort": 8443,
        "protocol": "tcp"
      }],
      "cpu": 0,
      "image": "${local.images.file_scanner}",
      "mountPoints": [],
      "volumesFrom": [],
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
          "awslogs-region": "eu-west-1",
          "awslogs-stream-prefix": "${aws_iam_role.scan.name}"
        }
      },
      "environment": [
        { "name": "SERVICE_ENABLE_UWSGI", "value": "yes" },
        { "name": "SSL_CERT_FILENAME", "value": "/etc/ssl/self-signed.crt" },
        { "name": "SSL_KEY_FILENAME", "value": "/etc/ssl/self-signed.key" },
        { "name": "REDIS_URL", "value": "redis://localhost:6379/0" }
      ]
  }
  
EOF


  file_scanner_worker_container = <<EOF
  {
      "name": "worker",
      "essential": true,
      "cpu": 0,
      "image": "${local.images.file_scanner}",
      "mountPoints": [],
      "volumesFrom": [],
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
          "awslogs-region": "eu-west-1",
          "awslogs-stream-prefix": "${aws_iam_role.scan.name}"
        }
      },
      "environment": [
        { "name": "SERVICE_ENABLE_CELERY", "value": "yes" },
        { "name": "SERVICE_ENABLE_FRESHCLAM", "value": "yes" },
        { "name": "SERVICE_ENABLE_CLAMD", "value": "yes" },
        { "name": "REDIS_URL", "value": "redis://localhost:6379/0" }
      ]
  }
  
EOF


  file_scanner_redis_container = <<EOF
  {
      "name": "redis",
      "essential": true,
      "cpu": 0,
      "image": "redis:5",
      "mountPoints": [],
      "volumesFrom": [],
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

