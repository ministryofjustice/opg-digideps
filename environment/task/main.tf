resource "aws_security_group" "task" {
  name_prefix = aws_ecs_task_definition.task.family
  vpc_id      = var.vpc_id
  tags = merge(
    var.default_tags,
    {
      "Name" = "${var.name}-${var.environment}"
    },
  )

  lifecycle {
    create_before_destroy = true
  }
}

data "aws_vpc_endpoint" "ecr_endpoint" {
  service_name = "com.amazonaws.eu-west-1.ecr.dkr"
  vpc_id       = var.vpc_id
}

data "aws_vpc_endpoint" "logs_endpoint" {
  service_name = "com.amazonaws.eu-west-1.logs"
  vpc_id       = var.vpc_id
}

data "aws_vpc_endpoint" "s3_endpoint" {
  service_name = "com.amazonaws.eu-west-1.s3"
  vpc_id       = var.vpc_id
}

resource "aws_security_group_rule" "task_https_out_ecr" {
  for_each = toset(data.aws_vpc_endpoint.ecr_endpoint.security_group_ids)

  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.task.id
  source_security_group_id = each.value
}

resource "aws_security_group_rule" "task_https_out_logs" {
  for_each = toset(data.aws_vpc_endpoint.logs_endpoint.security_group_ids)

  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.task.id
  source_security_group_id = each.value
}

resource "aws_security_group_rule" "task_https_out_s3" {
  type              = "egress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = aws_security_group.task.id
  prefix_list_ids   = [data.aws_vpc_endpoint.s3_endpoint.prefix_list_id]
}

resource "aws_ecs_task_definition" "task" {
  family                   = "${var.name}-${var.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = var.cpu
  memory                   = var.memory
  container_definitions    = var.container_definitions
  task_role_arn            = var.task_role_arn
  execution_role_arn       = var.execution_role_arn
  tags                     = var.default_tags
}
