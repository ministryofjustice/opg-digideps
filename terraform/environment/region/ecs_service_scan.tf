resource "aws_ecs_task_definition" "scan" {
  family                   = "scan-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 1024
  memory                   = var.account.memory_high
  container_definitions    = "[${local.file_scanner_rest_container}]"
  task_role_arn            = aws_iam_role.scan.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = var.default_tags
}

resource "aws_ecs_service" "scan" {
  name                    = aws_ecs_task_definition.scan.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.scan.arn
  desired_count           = var.account.scan_count
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true

  network_configuration {
    security_groups  = [module.scan_security_group.id]
    subnets          = data.aws_subnet.private[*].id
    assign_public_ip = false
  }

  service_connect_configuration {
    enabled   = true
    namespace = aws_service_discovery_http_namespace.cloudmap_namespace.arn
    service {
      discovery_name = "scan"
      port_name      = "scan-port"
      client_alias {
        dns_name = "scan"
        port     = 8080
      }
    }
  }

  capacity_provider_strategy {
    capacity_provider = local.capacity_provider
    weight            = 1
  }

  deployment_controller {
    type = "ECS"
  }

  deployment_circuit_breaker {
    enable   = false
    rollback = false
  }

  tags = var.default_tags
}

locals {
  file_scanner_rest_container = jsonencode(
    {
      name      = "rest",
      essential = true,
      portMappings = [{
        name          = "scan-port",
        containerPort = 8080,
        hostPort      = 8080,
        protocol      = "tcp"
      }],
      cpu         = 0,
      image       = local.images.file-scanner,
      mountPoints = [],
      volumesFrom = [],
      healthCheck = {
        command = [
          "CMD-SHELL",
          "wget --no-verbose --tries=1 --spider http://localhost:8080/health-check || exit 1"
        ],
        interval = 30,
        timeout  = 10,
        retries  = 3
      },
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = aws_iam_role.scan.name
        }
      }
    }
  )
}
