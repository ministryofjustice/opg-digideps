data "aws_iam_role" "sync" {
  name = "sync"
}

data "aws_s3_bucket" "backup" {
  bucket   = "backup.complete-deputy-report.service.gov.uk"
  provider = aws.management
}

data "aws_iam_policy_document" "sync_put_parameter_ssm" {
  statement {
    sid    = "AllowPutSSMParameters"
    effect = "Allow"
    actions = [
      "ssm:PutParameter"
    ]
    resources = [
      aws_ssm_parameter.flag_document_sync.arn,
    ]
  }
}
