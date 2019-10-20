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

resource "aws_security_group_rule" "scan_http_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 8080
  to_port                  = 8080
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
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
          "awslogs-region": "eu-west-1",
          "awslogs-stream-prefix": "${aws_iam_role.scan.name}"
        }
      },
      "links": ["server"],
      "environment": [
        { "name": "CLAMD_HOST", "value": "server" }
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

