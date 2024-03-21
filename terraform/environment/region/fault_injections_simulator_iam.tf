# Create role for running experiments
resource "aws_iam_role" "fault_injection_simulator" {
  name               = "fault-injection-simulator-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.fault_injection_simulator_assume.json
}

data "aws_iam_policy_document" "fault_injection_simulator_assume" {
  statement {
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["fis.amazonaws.com"]
    }
  }
}

# Add permissions for FIS to run experiments (ECS, Logging, SSM)
resource "aws_iam_role_policy_attachment" "fault_injection_simulator_ecs_access" {
  role       = aws_iam_role.fault_injection_simulator.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSFaultInjectionSimulatorECSAccess"
}

resource "aws_iam_role_policy_attachment" "fault_injection_simulator_ssm_access" {
  role       = aws_iam_role.fault_injection_simulator.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AWSFaultInjectionSimulatorSSMAccess"
}

resource "aws_iam_role_policy_attachment" "cloudwatch_logs_full_access" {
  role       = aws_iam_role.fault_injection_simulator.name
  policy_arn = "arn:aws:iam::aws:policy/CloudWatchLogsFullAccess"
}

resource "aws_iam_role_policy" "fault_injection_simulator_create_fis_service_linked_role" {
  name   = "create-fis-service-linked-role-permissions"
  role   = aws_iam_role.fault_injection_simulator.name
  policy = data.aws_iam_policy_document.fault_injection_simulator_create_fis_service_linked_role.json
}

data "aws_iam_policy_document" "fault_injection_simulator_create_fis_service_linked_role" {
  policy_id = "fix experiment permissions"
  statement {
    sid       = "AllowServiceLinkedRole"
    effect    = "Allow"
    resources = ["*"] #tfsec:ignore:aws-iam-no-policy-wildcards
    actions = [
      "iam:CreateServiceLinkedRole",
    ]
    condition {
      test     = "StringLike"
      variable = "iam:AWSServiceName"
      values   = ["fis.amazonaws.com"]
    }
    condition {
      test     = "StringEquals"
      variable = "aws:SourceAccount"
      values   = [data.aws_caller_identity.current.account_id]
    }
    condition {
      test     = "ArnLike"
      variable = "aws:SourceArn"
      values   = ["arn:aws:fis:*:${data.aws_caller_identity.current.account_id}:experiment/*"]
    }
  }
}

# Create role for registering instance
resource "aws_iam_role" "ssm_register_instance" {
  name               = "ssm-register-instance-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.ssm_register_instance_assume.json
}

data "aws_iam_policy_document" "ssm_register_instance_assume" {
  statement {
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["ssm.amazonaws.com"]
    }
  }
}

resource "aws_iam_role_policy" "ssm_register_instance_permissions" {
  name   = "ssm-register-instance-permissions"
  role   = aws_iam_role.fault_injection_simulator.name
  policy = data.aws_iam_policy_document.ssm_register_instance_permissions.json
}

data "aws_iam_policy_document" "ssm_register_instance_permissions" {
  policy_id = "ssm instance activation permissions"
  statement {
    sid       = "AllowSSMCommands"
    effect    = "Allow"
    resources = ["*"] #tfsec:ignore:aws-iam-no-policy-wildcards
    actions = [
      "ssm:CreateActivation",
      "ssm:AddTagsToResource",
    ]
  }

  statement {
    sid       = "ManagedInstancePermissions"
    effect    = "Allow"
    resources = ["*"] #tfsec:ignore:aws-iam-no-policy-wildcards
    actions = [
      "ssm:DeleteActivation",
      "ssm:DeregisterManagedInstance",
    ]
  }
}
