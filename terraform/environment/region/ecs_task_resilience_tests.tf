locals {
  fis_arn_prefix              = "arn:aws:fis:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}"
  template_arn_prefix         = "${local.fis_arn_prefix}:experiment-template"
  ecs_stop_frontend_arn       = var.account.fault_injection_experiments_enabled ? "${local.template_arn_prefix}/${module.fault_injection_simulator_experiments[0].ecs_stop_frontend_tasks_template_id}" : ""
  ecs_stress_cpu_frontend_arn = var.account.fault_injection_experiments_enabled ? "${local.template_arn_prefix}/${module.fault_injection_simulator_experiments[0].ecs_front_cpu_stress_template_id}" : ""
  ecs_stress_io_frontend_arn  = var.account.fault_injection_experiments_enabled ? "${local.template_arn_prefix}/${module.fault_injection_simulator_experiments[0].front_io_stress_template_id}" : ""
  experiment_resources = var.account.fault_injection_experiments_enabled ? [
    local.ecs_stop_frontend_arn,
    local.ecs_stress_cpu_frontend_arn,
    local.ecs_stress_io_frontend_arn,
    "arn:aws:fis:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:experiment/*"
  ] : ["arn:aws:fis:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:experiment/*"]
}

resource "aws_iam_role" "resilience_tests" {
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  name               = "resilience-tests.${local.environment}"
  tags               = var.default_tags
}

data "aws_iam_policy_document" "resilience_tests" {
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

  statement {
    sid    = "AllowFISRunExperiments"
    effect = "Allow"
    actions = [
      "fis:StartExperiment"
    ]
    resources = local.experiment_resources
  }

  statement {
    sid    = "CreateServiceLinkedRole"
    effect = "Allow"
    actions = [
      "iam:CreateServiceLinkedRole"
    ]
    resources = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/aws-service-role/fis.amazonaws.com/AWSServiceRoleForFIS"]
  }
}

resource "aws_iam_role_policy" "resilience_tests" {
  name   = "resilience-tests.${local.environment}"
  role   = aws_iam_role.resilience_tests.id
  policy = data.aws_iam_policy_document.resilience_tests.json
}

locals {
  resilience_tests_sg_rules = {
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
module "resilience_tests_security_group" {
  source      = "./modules/security_group"
  name        = "resilience-tests"
  description = "Resilience Test SG Rules"
  rules       = local.resilience_tests_sg_rules
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

# Needs a reasonable amount of resource for multi-threaded on the browser
module "resilience_tests" {
  source = "./modules/task"
  name   = "resilience-tests"

  cluster_name          = aws_ecs_cluster.main.name
  cpu                   = 2048
  memory                = 8192
  container_definitions = "[${local.resilience_tests}]"
  tags                  = var.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution_role.arn
  subnet_ids            = data.aws_subnet.private[*].id
  task_role_arn         = aws_iam_role.resilience_tests.arn
  security_group_id     = module.resilience_tests_security_group.id
}

locals {
  resilience_tests = jsonencode({
    cpu       = 0,
    essential = true,
    image     = local.images.orchestration,
    command   = ["sh", "./tests/run-resilience-tests.sh"],
    name      = "resilience-tests",
    logConfiguration = {
      logDriver = "awslogs",
      options = {
        awslogs-group         = aws_cloudwatch_log_group.opg_digi_deps.name,
        awslogs-region        = "eu-west-1",
        awslogs-stream-prefix = "resilience-tests"
      }
    },
    environment = concat(local.fis_template_variables,
      [
        { name = "ADMIN_URL", value = "https://${var.admin_fully_qualified_domain_name}" },
        { name = "FRONT_URL", value = "https://${var.front_fully_qualified_domain_name}" },
        { name = "ENVIRONMENT", value = var.secrets_prefix },
        { name = "LOG_AND_CONTINUE", value = "true" },
        { name = "TASK_TIMINGS_LOG", value = "tests/resilience-tests/task_timings.csv" },
        { name = "TASK_ERROR_LOG", value = "tests/resilience-tests/task_errors.csv" }
    ])
  })
}
