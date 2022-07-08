resource "aws_iam_role" "api" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "api.${local.environment}"
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "api_task_logs" {
  name   = "api-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.api.id
}

resource "aws_iam_role_policy" "api_query_ssm" {
  name   = "api-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.api_query_ssm.json
  role   = aws_iam_role.api.id
}

data "aws_iam_policy_document" "api_query_ssm" {
  statement {
    sid    = "AllowQuerySSMParameters"
    effect = "Allow"
    actions = [
      "ssm:GetParameter"
    ]
    resources = [
      aws_ssm_parameter.flag_document_sync.arn,
      aws_ssm_parameter.document_sync_row_limit.arn,
      aws_ssm_parameter.flag_checklist_sync.arn,
      aws_ssm_parameter.checklist_sync_row_limit.arn
    ]
  }
}
