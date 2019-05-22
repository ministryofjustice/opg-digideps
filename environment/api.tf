resource "aws_iam_role" "api" {
  assume_role_policy = "${data.aws_iam_policy_document.task_role_assume_policy.json}"
  name               = "api.${terraform.workspace}"
}

resource "aws_iam_role_policy" "api_s3_backups" {
  name   = "api-s3-access.${terraform.workspace}"
  policy = "${data.aws_iam_policy_document.s3_backups.json}"
  role   = "${aws_iam_role.api.id}"
}
