resource "aws_iam_role" "admin" {
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
  name               = "admin.${local.environment}"
  tags               = var.default_tags
}

resource "aws_iam_role_policy" "admin_s3" {
  name   = "admin-s3.${local.environment}"
  policy = data.aws_iam_policy_document.admin_s3.json
  role   = aws_iam_role.admin.id
}

#tfsec:ignore:aws-iam-no-policy-wildcards - wildcards for objects in individual buckets not overly permissive
data "aws_iam_policy_document" "admin_s3" {
  statement {
    sid    = "AllAdminActionsCalledOnS3Bucket"
    effect = "Allow"
    actions = [
      "s3:GetObject",
    ]
    resources = [
      module.pa_uploads.arn,
      "${module.pa_uploads.arn}/*",
      "arn:aws:s3:::digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk",
      "arn:aws:s3:::digideps.${var.account.sirius_environment}.eu-west-1.sirius.opg.justice.gov.uk/*"
    ]
  }
}

resource "aws_iam_role_policy" "admin_query_ssm" {
  name   = "admin-query-ssm.${local.environment}"
  policy = data.aws_iam_policy_document.sirius_files_sync_query_ssm.json
  role   = aws_iam_role.admin.id
}

resource "aws_iam_role_policy" "admin_task_logs" {
  name   = "admin-task-logs.${local.environment}"
  policy = data.aws_iam_policy_document.ecs_task_logs.json
  role   = aws_iam_role.admin.id
}
