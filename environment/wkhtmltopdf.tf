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
}

resource "aws_iam_role" "wkhtmltopdf" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "wkhtmltopdf.${terraform.workspace}"
  tags               = local.default_tags
}

resource "aws_ecs_task_definition" "wkhtmltopdf" {
  family                   = "wkhtmltopdf-${terraform.workspace}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
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
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"

  network_configuration {
    security_groups  = [aws_security_group.wkhtmltopdf.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }

  service_registries {
    registry_arn = aws_service_discovery_service.wkhtmltopdf.arn
  }

  tags = local.default_tags
}

resource "aws_security_group" "wkhtmltopdf" {
  name_prefix = aws_ecs_task_definition.wkhtmltopdf.family
  vpc_id      = data.aws_vpc.vpc.id

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(
    local.default_tags,
    {
      "Name" = "wkhtmltopdf"
    },
  )
}

resource "aws_security_group_rule" "wkhtmltopdf_front_http_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 80
  to_port                  = 80
  security_group_id        = aws_security_group.wkhtmltopdf.id
  source_security_group_id = aws_security_group.front.id
}

resource "aws_security_group_rule" "wkhtmltopdf_admin_http_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 80
  to_port                  = 80
  security_group_id        = aws_security_group.wkhtmltopdf.id
  source_security_group_id = aws_security_group.admin.id
}

resource "aws_security_group_rule" "wkhtmltopdf_out" {
  type              = "egress"
  protocol          = "-1"
  from_port         = 0
  to_port           = 0
  cidr_blocks       = ["0.0.0.0/0"]
  security_group_id = aws_security_group.wkhtmltopdf.id
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

