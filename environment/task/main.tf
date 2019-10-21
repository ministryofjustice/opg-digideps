data "aws_iam_role" "task" {
  name = "task"
}

resource "aws_security_group" "task" {
  name_prefix = aws_ecs_task_definition.task.family
  vpc_id      = var.vpc_id
  tags        = var.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "task_out" {
  type      = "egress"
  protocol  = "-1"
  from_port = 0
  to_port   = 0

  security_group_id = aws_security_group.task.id
  cidr_blocks       = ["0.0.0.0/0"]
}

resource "aws_ecs_task_definition" "task" {
  family                   = "${var.name}-${var.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  container_definitions    = var.container_definitions
  task_role_arn            = data.aws_iam_role.task.arn
  execution_role_arn       = var.execution_role_arn
  tags                     = var.default_tags
}
