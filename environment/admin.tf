resource "aws_iam_role" "admin" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "admin.${local.environment}"
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "admin_s3" {
  name   = "admin-s3.${local.environment}"
  policy = data.aws_iam_policy_document.admin_s3.json
  role   = aws_iam_role.admin.id
}

data "aws_iam_policy_document" "admin_s3" {
  statement {
    sid    = "AllAdminActionsCalledOnS3Bucket"
    effect = "Allow"
    actions = [
      "s3:GetObject",
    ]
    resources = [
      "${aws_s3_bucket.pa_uploads.arn}",
      "${aws_s3_bucket.pa_uploads.arn}/*",
    ]
  }
}
