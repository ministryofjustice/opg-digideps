# Create encrypted logging for fault injection experiments
data "aws_kms_alias" "cloudwatch_application_logs_encryption" {
  name = "alias/digideps_logs_encryption_key"
}

resource "aws_cloudwatch_log_group" "fis_app_ecs_tasks" {
  name              = "/aws/fis/app-ecs-tasks-experiment-${data.aws_default_tags.current.tags.environment-name}"
  retention_in_days = 7
  kms_key_id        = data.aws_kms_alias.cloudwatch_application_logs_encryption.target_key_arn
}

# Add resource policy to allow FIS or the FIS role to write logs

data "aws_iam_policy_document" "cloudwatch_log_group_policy_fis_app_ecs_tasks" {
  statement {
    sid    = "AWSLogDeliveryWrite"
    effect = "Allow"

    principals {
      identifiers = [
        "delivery.logs.amazonaws.com"
      ]
      type = "Service"
    }

    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents"
    ]

    resources = [
      "${aws_cloudwatch_log_group.fis_app_ecs_tasks.arn}:*",
    ]

    condition {
      test     = "StringEquals"
      variable = "aws:SourceAccount"
      values   = [data.aws_caller_identity.current.account_id]
    }
  }
  statement {
    sid    = "AWSLogEncrypt"
    effect = "Allow"

    principals {
      identifiers = [
        "delivery.logs.amazonaws.com"
      ]
      type = "Service"
    }

    actions = [
      "kms:Encrypt",
      "kms:GenerateDataKey",
    ]

    resources = [
      data.aws_kms_alias.cloudwatch_application_logs_encryption.target_key_arn
    ]

    condition {
      test     = "StringEquals"
      variable = "aws:SourceAccount"
      values   = [data.aws_caller_identity.current.account_id]
    }
  }
}

resource "aws_cloudwatch_log_resource_policy" "fis_app_ecs_tasks" {
  policy_document = data.aws_iam_policy_document.cloudwatch_log_group_policy_fis_app_ecs_tasks.json
  policy_name     = "fis_app_ecs_tasks_logging"
}

# Add log encryption and log write/delivery permissions to the FIS role

data "aws_iam_policy_document" "fis_role_log_encryption" {
  policy_id = "log_access"
  statement {
    sid = "AllowCloudWatchLogsEncryption"
    actions = [
      "kms:Encrypt",
      "kms:GenerateDataKey",
    ]

    resources = [
      data.aws_kms_alias.cloudwatch_application_logs_encryption.target_key_arn
    ]
  }

  statement {
    sid    = "AllowCloudWatchLogs"
    effect = "Allow"
    actions = [
      "logs:CreateLogDelivery",
      "logs:DescribeLogGroups",
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeResourcePolicies",
      "logs:PutResourcePolicy",
    ]

    resources = [
      aws_cloudwatch_log_group.fis_app_ecs_tasks.arn,
      "${aws_cloudwatch_log_group.fis_app_ecs_tasks.arn}:*",
    ]
  }
}

resource "aws_iam_role_policy" "fis_role_log_encryption" {
  name   = "fis-role-log-permissions"
  role   = var.fault_injection_simulator_role.name
  policy = data.aws_iam_policy_document.fis_role_log_encryption.json
}

# Create experiment template for ECS tasks

resource "aws_fis_experiment_template" "ecs_stop_frontend_tasks" {
  description = "Stop one ECS task in the frontend service"
  role_arn    = var.fault_injection_simulator_role.arn
  tags = {
    Name = "${data.aws_default_tags.current.tags.environment-name} - Stop ECS Task"
  }

  action {
    action_id   = "aws:ecs:stop-task"
    name        = "stop-one-task"
    start_after = []

    target {
      key   = "Tasks"
      value = "one-task"
    }
  }

  stop_condition {
    source = "none"
    value  = null
  }

  log_configuration {
    log_schema_version = 2

    cloudwatch_logs_configuration {
      log_group_arn = "${aws_cloudwatch_log_group.fis_app_ecs_tasks.arn}:*" # tfsec:ignore:aws-cloudwatch-log-group-wildcard
    }
  }

  target {
    name = "one-task"
    resource_tag {
      key   = "environment-name"
      value = data.aws_default_tags.current.tags.environment-name
    }
    parameters = {
      "cluster" = var.ecs_cluster
      "service" = "front-${var.environment}"
    }
    resource_type  = "aws:ecs:task"
    selection_mode = "COUNT(1)"
  }
}

resource "aws_fis_experiment_template" "ecs_front_cpu_stress" {
  description = "Stress CPU for all ECS task in the front service"
  role_arn    = var.fault_injection_simulator_role.arn
  tags = {
    Name = "${data.aws_default_tags.current.tags.environment-name} - Stress ECS Task CPU"
  }


  action {
    action_id   = "aws:ecs:task-cpu-stress"
    description = null
    name        = "cpu_stress_100_percent_10_mins"
    start_after = []
    parameter {
      key   = "duration"
      value = "PT10M"
    }
    target {
      key   = "Tasks"
      value = "all-tasks"
    }
  }

  stop_condition {
    source = "none"
    value  = null
  }

  log_configuration {
    log_schema_version = 2

    cloudwatch_logs_configuration {
      log_group_arn = "${aws_cloudwatch_log_group.fis_app_ecs_tasks.arn}:*" # tfsec:ignore:aws-cloudwatch-log-group-wildcard
    }
  }

  target {
    name = "all-tasks"
    resource_tag {
      key   = "environment-name"
      value = data.aws_default_tags.current.tags.environment-name
    }
    parameters = {
      "cluster" = var.ecs_cluster
      "service" = "front-${var.environment}"
    }
    resource_type  = "aws:ecs:task"
    selection_mode = "ALL"
  }
}

resource "aws_fis_experiment_template" "ecs_front_io_stress" {
  description = "Stress IO for all ECS task in the front service"
  role_arn    = var.fault_injection_simulator_role.arn
  tags = {
    Name = "${data.aws_default_tags.current.tags.environment-name} - Stress ECS Task IO"
  }

  action {
    action_id   = "aws:ecs:task-io-stress"
    description = null
    name        = "io_stress_10_mins"
    start_after = []
    parameter {
      key   = "duration"
      value = "PT10M"
    }
    target {
      key   = "Tasks"
      value = "all-tasks"
    }
  }

  stop_condition {
    source = "none"
    value  = null
  }

  log_configuration {
    log_schema_version = 2

    cloudwatch_logs_configuration {
      log_group_arn = "${aws_cloudwatch_log_group.fis_app_ecs_tasks.arn}:*" # tfsec:ignore:aws-cloudwatch-log-group-wildcard
    }
  }

  target {
    name = "all-tasks"
    resource_tag {
      key   = "environment-name"
      value = data.aws_default_tags.current.tags.environment-name
    }
    parameters = {
      "cluster" = var.ecs_cluster
      "service" = "front-${var.environment}"
    }
    resource_type  = "aws:ecs:task"
    selection_mode = "ALL"
  }
}
