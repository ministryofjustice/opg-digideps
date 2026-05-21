resource "aws_iam_role" "htmltopdf" {
  assume_role_policy   = data.aws_iam_policy_document.task_role_assume_policy.json
  name                 = "htmltopdf.${local.environment}"
  permissions_boundary = data.aws_iam_policy.default_boundary.arn
  tags                 = var.default_tags
}
