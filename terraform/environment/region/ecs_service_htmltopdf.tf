resource "aws_ecs_task_definition" "htmltopdf" {
  family                   = "htmltopdf-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = var.account.cpu_low
  memory                   = var.account.memory_low
  container_definitions    = "[${local.htmltopdf_container}]"
  task_role_arn            = aws_iam_role.htmltopdf.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = var.default_tags
}

resource "aws_ecs_service" "htmltopdf" {
  name                    = aws_ecs_task_definition.htmltopdf.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.htmltopdf.arn
  desired_count           = 1
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true

  network_configuration {
    security_groups  = [module.htmltopdf_security_group.id]
    subnets          = data.aws_subnet.private[*].id
    assign_public_ip = false
  }

  service_connect_configuration {
    enabled   = true
    namespace = aws_service_discovery_http_namespace.cloudmap_namespace.arn
    service {
      discovery_name = "htmltopdf"
      port_name      = "htmltopdf-port"
      client_alias {
        dns_name = "htmltopdf"
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
  htmltopdf_container = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = local.images.htmltopdf,
      mountPoints = [],
      name        = "htmltopdf",
      portMappings = [{
        name          = "htmltopdf-port",
        containerPort = 8080,
        hostPort      = 8080,
        protocol      = "tcp"
      }],
      volumesFrom = [],
      healthCheck = {
        command = [
          "CMD-SHELL",
          "curl --fail -X POST -H 'Content-Type:application/json' -d '{\"contents\":\"dGVzdA==\"}' -o /dev/null http://localhost:8080/ || exit 1"
        ],
        interval = 30,
        timeout  = 5,
        retries  = 3
      },
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = aws_iam_role.htmltopdf.name
        }
      }
    }
  )
}
