resource "aws_iam_role" "htmltopdf" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "htmltopdf.${local.environment}"
  tags               = var.default_tags
}

resource "aws_iam_role_policy" "htmltopdf_s3" {
  name   = "htmltopdf-s3.${local.environment}"
  policy = data.aws_iam_policy_document.htmltopdf_s3.json
  role   = aws_iam_role.htmltopdf.id
}

data "aws_iam_policy_document" "htmltopdf_s3" {
  statement {
    sid    = "AllFrontActionsCalledOnS3Bucket"
    effect = "Allow"
    actions = [
      "s3:GetObject",
      "s3:DeleteObject",
      "s3:DeleteObjectVersion",
      "s3:ListBucketVersions",
      "s3:PutObject",
      "s3:GetObjectTagging",
      "s3:PutObjectTagging",
      "s3:ListBucket"
    ]
    #trivy:ignore:avd-aws-0057 - Not overly permissive
    resources = [
      module.pa_uploads.arn,
      "${module.pa_uploads.arn}/*",
    ]
  }
}
