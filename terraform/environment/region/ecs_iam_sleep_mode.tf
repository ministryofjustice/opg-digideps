resource "aws_iam_role" "sleep_mode" {
  name               = "sleep-mode.${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  tags               = var.default_tags
}

data "aws_iam_policy_document" "sleep_mode" {
  statement {
    sid    = "StopAndStartRDSCluster"
    effect = "Allow"
    actions = [
      "rds:StopDBCluster",
      "rds:StartDBCluster"
    ]
    resources = [
      module.api_aurora.cluster_arn
    ]
  }

  statement {
    sid    = "StopAndStartECSCluster"
    effect = "Allow"
    actions = [
      "ecs:ListServices",
      "ecs:DescribeServices",
      "ecs:UpdateService"
    ]
    resources = [
      "arn:aws:ecs:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:cluster/${local.environment}",
      "arn:aws:ecs:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:service/${local.environment}/*"
    ]
  }
}

resource "aws_iam_role_policy" "sleep_mode" {
  name   = "sleep-mode.${local.environment}"
  policy = data.aws_iam_policy_document.sleep_mode.json
  role   = aws_iam_role.sleep_mode.id
}
