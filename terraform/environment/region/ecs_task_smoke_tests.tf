resource "aws_iam_role" "smoke_tests" {
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  name               = "smoke-tests.${local.environment}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "smoke_tests" {
  statement {
    sid    = "AllowQuerySecretsManager"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue"
    ]
    resources = [
      aws_secretsmanager_secret.smoke.arn
    ]
  }
}

resource "aws_iam_role_policy" "smoke_tests" {
  name   = "smoke-tests.${local.environment}"
  policy = data.aws_iam_policy_document.smoke_tests.json
  role   = aws_iam_role.smoke_tests.id
}

module "smoke_tests" {
  source = "./modules/task"
  name   = "smoke-tests"

  cluster_name          = aws_ecs_cluster.main.name
  container_definitions = "[${local.smoke_tests}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = aws_iam_role.smoke_tests.arn
  vpc_id                = data.aws_vpc.vpc.id
  security_group_id     = module.integration_tests_security_group.id
}

locals {
  smoke_tests = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.orchestration,
    command   = ["sh", "./smoke-tests/run-smoke-tests.sh"],
    name      = "smoke-tests",
    logConfiguration = {
      logDriver = "awslogs",
      options = {
        awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
        awslogs-region        = "eu-west-1",
        awslogs-stream-prefix = "smoke-tests"
      }
    },
    environment = [
      { name = "ADMIN_URL", value = "http://admin" },
      { name = "FRONT_URL", value = "http://front" },
      { name = "ENVIRONMENT", value = var.secrets_prefix }
    ]
  })
}
