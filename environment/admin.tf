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
      aws_s3_bucket.pa_uploads.arn,
      "${aws_s3_bucket.pa_uploads.arn}/*",
    ]
  }
}

data "aws_iam_policy_document" "admin_put_parameter_ssm" {
  statement {
    sid    = "AllowPutSSMParameters"
    effect = "Allow"
    actions = [
      "ssm:PutParameter",
      "ssm:GetParameter"
    ]
    resources = [
      aws_ssm_parameter.flag_document_sync.arn,
    ]
  }
}

resource "aws_iam_role_policy" "admin_query_ssm" {
  name   = "admin-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.front_query_ssm.json
  role   = aws_iam_role.admin.id
}

resource "aws_iam_role_policy" "admin_task_logs" {
  name   = "admin-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.admin.id
}

resource "aws_iam_role_policy" "admin_put_parameter_ssm_integration_tests" {
  name   = "admin-put-parameter-ssm-integration-tests.${local.environment}"
  policy = data.aws_iam_policy_document.admin_put_parameter_ssm.json
  role   = data.aws_iam_role.sync.id
}
