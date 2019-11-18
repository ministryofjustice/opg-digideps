module "client_unit_test" {
  source = "./task"
  name   = "client-unit-test"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.client_unit_test_container}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = data.aws_iam_role.sync.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.client_unit_test_security_group.id
}

module "client_unit_test_security_group" {
  source = "./security_group"
  rules  = local.common_sg_rules
  name   = "client-unit-test"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  client_unit_test_container = <<EOF
  {
    "name": "client-unit-test",
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
