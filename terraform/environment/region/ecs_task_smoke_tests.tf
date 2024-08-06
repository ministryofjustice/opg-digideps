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
      data.aws_secretsmanager_secret.smoke_tests_variables.arn
    ]
  }
}

resource "aws_iam_role_policy" "smoke_tests" {
  name   = "smoke-tests.${local.environment}"
  policy = data.aws_iam_policy_document.smoke_tests.json
  role   = aws_iam_role.smoke_tests.id
}

locals {
  smoke_tests_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    frontend_access = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
  }
}

module "smoke_tests_security_group" {
  source      = "./modules/security_group"
  name        = "smoke-tests"
  description = "Smoke Test SG Rules"
  rules       = local.smoke_tests_sg_rules
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
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
  security_group_id     = module.smoke_tests_security_group.id
}

locals {
  smoke_tests = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.orchestration,
    command   = ["sh", "./tests/run-smoke-tests.sh"],
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
      { name = "ADMIN_URL", value = "https://${var.admin_fully_qualified_domain_name}" },
      { name = "FRONT_URL", value = "https://${var.front_fully_qualified_domain_name}" },
      { name = "ENVIRONMENT", value = var.secrets_prefix }
    ]
  })
}
