resource "aws_ecs_task_definition" "mock_sirius_integration" {
  family                   = "mock-sirius-integration-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = var.account.cpu_low
  memory                   = var.account.memory_low
  container_definitions    = "[${local.mock_sirius_integration_container}]"
  task_role_arn            = aws_iam_role.front.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  runtime_platform {
    cpu_architecture        = "ARM64"
    operating_system_family = "LINUX"
  }
  tags = var.default_tags
}

resource "aws_ecs_service" "mock_sirius_integration" {
  name                    = aws_ecs_task_definition.mock_sirius_integration.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.mock_sirius_integration.arn
  desired_count           = local.environment == "production02" ? 0 : 1
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true
  tags                    = var.default_tags

  network_configuration {
    security_groups  = [module.mock_sirius_integration_security_group.id]
    subnets          = data.aws_subnet.private[*].id
    assign_public_ip = false
  }

  service_connect_configuration {
    enabled   = true
    namespace = aws_service_discovery_http_namespace.cloudmap_namespace.arn
    service {
      discovery_name = "mock-sirius-integration"
      port_name      = "mock-sirius-integration-port"
      client_alias {
        dns_name = "mock-sirius-integration"
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
}

locals {
  mock_sirius_integration_container = jsonencode(
    {
      name  = "mock-sirius-integration",
      image = "muonsoft/openapi-mock:${local.openapi_mock_version}",
      portMappings = [{
        name          = "mock-sirius-integration-port",
        containerPort = 8080,
        hostPort      = 8080,
        protocol      = "tcp"
      }],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = aws_iam_role.mock_sirius_integration.name
        }
      },
      environment = [
        {
          name  = "OPENAPI_MOCK_SPECIFICATION_URL",
          value = "https://raw.githubusercontent.com/ministryofjustice/opg-data-deputy-reporting/master/lambda_functions/v2/openapi/deputy-reporting-openapi.yml"
        }
      ]
    }
  )
}
