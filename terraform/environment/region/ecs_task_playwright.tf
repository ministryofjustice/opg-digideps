resource "aws_iam_role" "end_to_end_tests" {
  assume_role_policy   = data.aws_iam_policy_document.ecs_task_assume_policy.json
  name                 = "end-to-end-tests.${local.environment}"
  permissions_boundary = data.aws_iam_policy.default_boundary.arn
  tags                 = var.default_tags
}

data "aws_iam_policy_document" "end_to_end_tests" {
  statement {
    sid    = "DecryptSecretKMS"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      data.aws_kms_alias.cloudwatch_application_secret_encryption.target_key_arn
    ]
  }
}

resource "aws_iam_role_policy" "end_to_end_tests" {
  name   = "end-to-end-tests.${local.environment}"
  policy = data.aws_iam_policy_document.end_to_end_tests.json
  role   = aws_iam_role.end_to_end_tests.id
}

locals {
  end_to_end_tests_sg_rules = {
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

#trivy:ignore:avd-aws-0104 - Currently needed in as no domain egress filtering
module "end_to_end_tests_security_group" {
  source      = "./modules/security_group"
  name        = "end-to-end-tests"
  description = "Playwright Test SG Rules"
  rules       = local.end_to_end_tests_sg_rules
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.main.id
  environment = local.environment
}

# Increased memory as it uses a headless browser
module "end_to_end_tests" {
  source = "./modules/task"
  name   = "end-to-end-tests"

  cluster_name          = aws_ecs_cluster.main.name
  cpu                   = 1024
  memory                = 2048
  container_definitions = "[${local.end_to_end_tests}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.application[*].id
  task_role_arn         = aws_iam_role.end_to_end_tests.arn
  architecture          = "ARM64"
  os                    = "LINUX"
  security_group_id     = module.end_to_end_tests_security_group.id
}

locals {
  end_to_end_tests = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.end_to_end,
    command   = ["test"],
    name      = "end-to-end-tests",
    logConfiguration = {
      logDriver = "awslogs",
      options = {
        awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
        awslogs-region        = "eu-west-1",
        awslogs-stream-prefix = "end-to-end-tests"
      }
    },
    environment = [
      { name = "ADMIN_URL", value = "https://${var.admin_fully_qualified_domain_name}" },
      { name = "FRONT_URL", value = "https://${var.front_fully_qualified_domain_name}" },
      { name = "ENVIRONMENT", value = var.secrets_prefix }
    ]
  })
}
