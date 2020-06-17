resource "aws_ecs_task_definition" "mock_sirius_integration" {
  family                   = "mock-sirius-integration-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.mock_sirius_integration_container}]"
  task_role_arn            = aws_iam_role.front.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_service" "mock_sirius_integration" {
  name                    = aws_ecs_task_definition.mock_sirius_integration.family
  cluster                 = aws_ecs_cluster.main.id
  task_definition         = aws_ecs_task_definition.mock_sirius_integration.arn
  launch_type             = "FARGATE"
  enable_ecs_managed_tags = true
  propagate_tags          = "SERVICE"
  tags                    = local.default_tags

  network_configuration {
    security_groups  = [module.mock_sirius_integration_security_group.id]
    subnets          = data.aws_subnet.private.*.id
    assign_public_ip = false
  }
}

locals {
  mock_sirius_integration_container = <<EOF
  {
    "name": "mock-sirius-integration",
    "image": "muonsoft/openapi-mock:latest",
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.mock_sirius_integration.name}"
      }
    },
    "environment": [
      { "name": "OPENAPI_MOCK_SPECIFICATION_URL", "value": "https://raw.githubusercontent.com/ministryofjustice/opg-data/master/docs/deputy-reporting-openapi-v1.yml" }
    ]
  }
EOF
}
