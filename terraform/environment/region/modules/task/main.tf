resource "aws_ecs_task_definition" "task" {
  family                   = "${var.name}-${var.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = var.cpu
  memory                   = var.memory
  container_definitions    = var.container_definitions
  task_role_arn            = var.task_role_arn
  execution_role_arn       = var.execution_role_arn
  runtime_platform {
    cpu_architecture        = var.architecture
    operating_system_family = var.os
  }
  tags = var.tags
}
