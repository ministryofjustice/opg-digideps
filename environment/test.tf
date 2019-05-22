resource "aws_iam_role" "test" {
  assume_role_policy = "${data.aws_iam_policy_document.task_role_assume_policy.json}"
  name               = "test.${terraform.workspace}"
}

resource "aws_iam_role_policy" "test_s3_backups" {
  name   = "test-s3-access.${terraform.workspace}"
  policy = "${data.aws_iam_policy_document.s3_backups.json}"
  role   = "${aws_iam_role.test.id}"
}
