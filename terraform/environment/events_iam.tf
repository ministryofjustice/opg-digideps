# Event Task Runner Role and Permissions
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

locals {
  events_task_dr_role_list = local.account.dr_backup ? [module.disaster_recovery_backup[0].task_role_arn] : []
  events_task_role_list = [
    aws_iam_role.front.arn,
    aws_iam_role.api.arn,
    data.aws_iam_role.sync.arn,
    aws_iam_role.execution_role.arn,
  ]
  combined_events_task_role_list = tolist(concat(local.events_task_role_list, local.events_task_dr_role_list))

  events_dr_task_list = local.account.dr_backup ? [module.disaster_recovery_backup[0].task_definition_arn] : []
  events_task_list = [
    aws_ecs_task_definition.check_csv_uploaded.arn,
    aws_ecs_task_definition.check_csv_uploaded.arn,
    aws_ecs_task_definition.checklist_sync.arn,
    aws_ecs_task_definition.api.arn,
    aws_ecs_task_definition.document_sync.arn,
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

# Event SNS Publisher Role and Permissions
#
#resource "aws_iam_role" "event_sns_publisher" {
#  name = "events-sns-publisher.${terraform.workspace}"
#  assume_role_policy = data.aws_iam_policy_document.sns_publisher_assume.json
#  tags               = local.default_tags
#}
#
#data "aws_iam_policy_document" "sns_publisher_assume" {
#  statement {
#    effect  = "Allow"
#    actions = ["sts:AssumeRole"]
#
#    principals {
#      identifiers = ["events.amazonaws.com"]
#      type        = "Service"
#    }
#  }
#}
#
#resource "aws_iam_policy" "event_sns_publisher" {
#  name        = "events-sns-publish.${local.environment}"
#  description = "Allow publishing to the SNS topic"
#
#  policy = data.aws_iam_policy_document.event_sns_publisher.json
#}
#
#data "aws_iam_policy_document" "event_sns_publisher" {
#  statement {
#    effect    = "Allow"
#    resources = [data.aws_sns_topic.alerts.arn]
#    actions = ["sns:Publish"]
#  }
#}
#
#resource "aws_iam_role_policy" "event_sns_publisher" {
#  name   = "events-sns-publisher.${local.environment}"
#  policy = data.aws_iam_policy_document.event_sns_publisher.json
#  role   = aws_iam_role.event_sns_publisher.id
#}
