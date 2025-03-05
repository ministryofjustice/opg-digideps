resource "aws_ecs_task_definition" "api" {
  family                   = "api-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.api_web}, ${local.api_container}]"
  task_role_arn            = aws_iam_role.api.arn
  execution_role_arn       = aws_iam_role.execution_role_db.arn
  runtime_platform {
    cpu_architecture        = "ARM64"
    operating_system_family = "LINUX"
  }
  tags = var.default_tags
}

resource "aws_ecs_service" "api" {
  name                    = aws_ecs_task_definition.api.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.api.arn
  desired_count           = var.account.task_count
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true
  tags                    = var.default_tags

  network_configuration {
    security_groups  = [module.api_service_security_group.id]
    subnets          = data.aws_subnet.private[*].id
    assign_public_ip = false
  }

  service_connect_configuration {
    enabled   = true
    namespace = aws_service_discovery_http_namespace.cloudmap_namespace.arn
    service {
      discovery_name = "api"
      port_name      = "api-port"
      client_alias {
        dns_name = "api"
        port     = 80
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
}

locals {
  api_web = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = local.images.api-webserver,
      mountPoints = [],
      name        = "api_web",
      portMappings = [{
        name          = "api-port",
        containerPort = 80,
        hostPort      = 80,
        protocol      = "tcp"
      }],
      healthCheck = {
        command : [
          "CMD-SHELL",
          "curl -f http://127.0.0.1:80/health-check || exit 1"
        ],
        interval = 30,
        timeout  = 5,
        retries  = 3
      },
      volumesFrom = [],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "${aws_iam_role.api.name}.web"
        }
      },
      environment = [
        { name = "APP_HOST", value = "127.0.0.1" },
        { name = "APP_PORT", value = "9000" }
      ]
    }
  )
  api_container = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = local.images.api,
      mountPoints = [],
      name        = "api_app",
      portMappings = [{
        containerPort = 9000,
        hostPort      = 9000,
        protocol      = "tcp"
      }],
      volumesFrom = [],
      stopTimeout = 60,
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "${aws_iam_role.api.name}.app"
        }
      },
      secrets = [
        {
          name      = "DATABASE_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        },
        {
          name      = "SECRET",
          valueFrom = data.aws_secretsmanager_secret.api_secret.arn
        },
        {
          name      = "SECRETS_ADMIN_KEY",
          valueFrom = data.aws_secretsmanager_secret.admin_api_client_secret.arn
        },
        {
          name      = "SECRETS_FRONT_KEY",
          valueFrom = data.aws_secretsmanager_secret.front_api_client_secret.arn
        }
      ],
      environment = concat(local.api_base_variables, local.api_service_variables)
    }
  )
}

# Additional definition for memory intensive commands (only app needed)

resource "aws_ecs_task_definition" "api_high_memory" {
  family                   = "api-high-memory-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 1024
  memory                   = 2048
  container_definitions    = "[${local.api_container}]"
  task_role_arn            = aws_iam_role.api.arn
  execution_role_arn       = aws_iam_role.execution_role_db.arn
  runtime_platform {
    cpu_architecture        = "ARM64"
    operating_system_family = "LINUX"
  }
  tags = var.default_tags
}
