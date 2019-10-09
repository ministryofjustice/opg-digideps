resource "aws_ecs_task_definition" "restore" {
  family                   = "restore-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  container_definitions    = "[${local.test_front_container}]"
  task_role_arn            = aws_iam_role.test.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}

resource "aws_ecs_task_definition" "backup" {
  family                   = "backup-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  container_definitions    = "[${local.test_front_container}]"
  task_role_arn            = aws_iam_role.test.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
  tags                     = local.default_tags
}
