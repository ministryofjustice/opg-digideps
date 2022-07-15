resource "aws_iam_role" "events_task_runner" {
  name               = "events-task-runner.${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.events_task_runner.json
  tags               = local.default_tags
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

#locals {
#  events_task_dr_role_list = local.account.dr_backup ? [module.disaster_recovery_backup.task_role_arn] : []
#  events_task_role_list = [
#      aws_iam_role.front.arn,
#      aws_iam_role.api.arn,
#      data.aws_iam_role.sync.arn,
#      aws_iam_role.execution_role.arn,
#  ]
#  combined_events_task_role_list = tolist(concat(local.events_task_role_list, local.events_task_dr_role_list))
#}

#local.combined_events_task_role_list

data "aws_iam_policy_document" "events_task_runner_policy" {
  statement {
    effect = "Allow"
    resources = [
      aws_iam_role.front.arn,
      aws_iam_role.api.arn,
      data.aws_iam_role.sync.arn,
      aws_iam_role.execution_role.arn,
      one(module.disaster_recovery_backup[*].task_role_arn)
    ]
    actions = [
      "iam:GetRole",
      "iam:PassRole"
    ]
  }
  statement {
    effect = "Allow"
    resources = [
      aws_ecs_task_definition.check_csv_uploaded.arn,
      aws_ecs_task_definition.check_csv_uploaded.arn,
      aws_ecs_task_definition.checklist_sync.arn,
      aws_ecs_task_definition.api.arn,
      aws_ecs_task_definition.document_sync.arn,
      one(module.disaster_recovery_backup[*].task_definition_arn),
    ]
    actions = ["ecs:RunTask"]
  }
}
