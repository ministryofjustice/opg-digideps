module "test_front" {
  source = "./task"
  name   = "test-front"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.test_front_container}]"
  default_tags          = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
}

locals {
  test_front_container = <<EOF
  {
    "name": "test_front",
    "image": "${local.images.client}",
    "command": [ "bin/phpunit", "-c", "tests/phpunit/" ],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "${aws_cloudwatch_log_group.opg_digi_deps.name}",
        "awslogs-region": "eu-west-1",
        "awslogs-stream-prefix": "${aws_iam_role.test.name}"
      }
    }
  }

EOF
}
