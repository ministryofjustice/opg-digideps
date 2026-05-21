resource "aws_iam_role" "mock_sirius_integration" {
  assume_role_policy   = data.aws_iam_policy_document.task_role_assume_policy.json
  name                 = "mock_sirius_integration.${local.environment}"
  permissions_boundary = data.aws_iam_policy.default_boundary.arn
  tags                 = var.default_tags
}
