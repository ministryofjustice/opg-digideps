locals {
  mock_sirius_integration_service_fqdn = "mock-sirius-integration.${aws_service_discovery_private_dns_namespace.private.name}"
}

resource "aws_service_discovery_service" "mock_sirius_integration" {
  name = "mock-sirius-integration"

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

resource "aws_ecs_task_definition" "mock_sirius_integration" {
  family                   = "mock-sirius-integration-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = local.account.cpu_low
  memory                   = local.account.memory_low
  container_definitions    = "[${local.mock_sirius_integration_container}]"
  task_role_arn            = aws_iam_role.front.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "mock_sirius_integration" {
  name                    = aws_ecs_task_definition.mock_sirius_integration.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.mock_sirius_integration.arn
  desired_count           = 1
  launch_type             = "FARGATE"
  platform_version        = "1.4.0"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  wait_for_steady_state   = true
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [module.mock_sirius_integration_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = true
  }

  service_registries {
    registry_arn = aws_service_discovery_service.mock_sirius_integration.arn
  }
}

locals {
  mock_sirius_integration_container = <<EOF
  {
    "name": "mock-sirius-integration",
    "image": "muonsoft/openapi-mock:latest",
    "portMappings": [{
          "containerPort": 8080,
          "hostPort": 8080,
          "protocol": "tcp"
    }],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.mock_sirius_integration.name}"
      }
    },
    "environment": [
      { "name": "OPENAPI_MOCK_SPECIFICATION_URL", "value": "https://raw.githubusercontent.com/ministryofjustice/opg-data-deputy-reporting/master/lambda_functions/v2/openapi/deputy-reporting-openapi.yml" }
    ]
  }
EOF
}
