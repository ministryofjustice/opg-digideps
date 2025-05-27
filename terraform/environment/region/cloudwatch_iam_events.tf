# Event Task Runner Role and Permissions
resource "aws_iam_role" "events_task_runner" {
  name               = "events-task-runner.${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.events_task_runner.json
  tags               = var.default_tags
}

data "aws_iam_policy_document" "events_task_runner" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["events.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "events_task_runner" {
  name   = "events-task-runner.${local.environment}"
  policy = data.aws_iam_policy_document.events_task_runner_policy.json
  role   = aws_iam_role.events_task_runner.id
}

locals {
  events_task_dr_role_list = var.account.dr_backup ? [module.disaster_recovery_backup[0].task_role_arn] : []
  events_task_role_list = [
    aws_iam_role.front.arn,
    aws_iam_role.api.arn,
    aws_iam_role.performance_data.arn,
    data.aws_iam_role.sync.arn,
    aws_iam_role.sleep_mode.arn,
    aws_iam_role.execution_role.arn,
    aws_iam_role.execution_role_db.arn
  ]
  combined_events_task_role_list = tolist(concat(local.events_task_role_list, local.events_task_dr_role_list))

  events_dr_task_list = var.account.dr_backup ? [module.disaster_recovery_backup[0].task_definition_arn] : []
  events_task_list = [
    aws_ecs_task_definition.check_csv_uploaded.arn,
    aws_ecs_task_definition.api.arn,
    aws_ecs_task_definition.api_high_memory.arn,
    module.analyse.task_definition_arn,
    module.performance_data.task_definition_arn,
    module.sleep_mode.task_definition_arn
  ]
  combined_events_task_list = tolist(concat(local.events_task_list, local.events_dr_task_list))
}

data "aws_iam_policy_document" "events_task_runner_policy" {
  statement {
    effect    = "Allow"
    resources = local.combined_events_task_role_list
    actions = [
      "iam:GetRole",
      "iam:PassRole"
    ]
  }
  statement {
    effect    = "Allow"
    resources = local.combined_events_task_list
    actions   = ["ecs:RunTask"]
  }
}
