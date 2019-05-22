resource "aws_iam_role" "admin" {
  assume_role_policy = "${data.aws_iam_policy_document.task_role_assume_policy.json}"
  name               = "admin.${terraform.workspace}"
  tags               = "${local.default_tags}"
}

resource "aws_iam_role_policy" "admin_s3_backups" {
  name   = "admin-s3-access.${terraform.workspace}"
  policy = "${data.aws_iam_policy_document.s3_backups.json}"
  role   = "${aws_iam_role.admin.id}"
}

resource "aws_iam_role_policy" "admin_s3_uploads_readdelete" {
  name   = "uploads-s3-readdelete.${terraform.workspace}"
  policy = "${data.aws_iam_policy_document.s3_uploads_readdelete.json}"
  role   = "${aws_iam_role.admin.id}"
}
