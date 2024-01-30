resource "aws_iam_role" "scan" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "scan.${local.environment}"
  tags               = var.default_tags
}