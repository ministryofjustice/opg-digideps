resource "aws_iam_role" "test" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "test.${terraform.workspace}"
}
