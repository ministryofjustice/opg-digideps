resource "aws_iam_role" "performance_data" {
  name               = "performance-data.${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.ecs_task_assume_policy.json
  tags               = var.default_tags
}

data "aws_iam_policy_document" "performance_data" {
  statement {
    sid    = "PerformanceDataS3Bucket"
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
    #trivy:ignore:avd-aws-0057 - Not overly permissive
    resources = [
      "arn:aws:s3:::opg-performance-data",
      "arn:aws:s3:::opg-performance-data/*",
    ]
  }
}

resource "aws_iam_role_policy" "performance_data" {
  name   = "performance-data.${local.environment}"
  policy = data.aws_iam_policy_document.performance_data.json
  role   = aws_iam_role.performance_data.id
}

resource "aws_iam_role_policy" "performance_data_task_logs" {
  name   = "performance-data-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.performance_data.id
}

resource "aws_iam_role_policy" "performance_data_query_ssm" {
  name   = "performance-data-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.api_permissions.json
  role   = aws_iam_role.performance_data.id
}
