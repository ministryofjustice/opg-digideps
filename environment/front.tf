resource "aws_iam_role" "front" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "front.${terraform.workspace}"
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "front_s3" {
  name   = "front-s3.${terraform.workspace}"
  policy = data.aws_iam_policy_document.front_s3.json
  role   = aws_iam_role.front.id
}

data "aws_iam_policy_document" "front_s3" {
  statement {
    sid    = "AllFrontActionsCalledOnS3Bucket"
    effect = "Allow"
    actions = [
      "s3:GetObject",
      "s3:DeleteObject",
      "s3:DeleteObjectVersion",
      "s3:ListObjectVersions",
      "s3:ListBucketVersions",
      "s3:PutObject",
      "s3:GetObjectTagging",
      "s3:PutObjectTagging",
    ]
    resources = [
      "${aws_s3_bucket.pa_uploads.arn}",
      "${aws_s3_bucket.pa_uploads.arn}/*",
    ]
  }
}
