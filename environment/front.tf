resource "aws_iam_role" "front" {
  assume_role_policy = "${data.aws_iam_policy_document.task_role_assume_policy.json}"
  name               = "front.${terraform.workspace}"
  tags               = "${local.default_tags}"
}

resource "aws_iam_role_policy" "front_s3_backups" {
  name   = "front-s3-access.${terraform.workspace}"
  policy = "${data.aws_iam_policy_document.s3_backups.json}"
  role   = "${aws_iam_role.front.id}"
}

resource "aws_iam_role_policy" "front_s3_uploads_writeonly" {
  name   = "uploads-s3-writeonly.${terraform.workspace}"
  policy = "${data.aws_iam_policy_document.s3_uploads_writeonly.json}"
  role   = "${aws_iam_role.front.id}"
}
