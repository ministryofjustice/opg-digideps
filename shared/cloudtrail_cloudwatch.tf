resource "aws_iam_role" "cloudtrail_cloudwatch" {
  name               = "CloudTrail_CloudWatchLogs_Role"
  assume_role_policy = data.aws_iam_policy_document.cloudtrail_cloudwatch_assume_role.json

  tags = local.default_tags
}

data "aws_iam_policy_document" "cloudtrail_cloudwatch_assume_role" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]
    principals {
      identifiers = ["cloudtrail.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "cloudtrail_cloudwatch" {
  policy = data.aws_iam_policy_document.cloudtrail_cloudwatch.json
  role   = aws_iam_role.cloudtrail_cloudwatch.id
}

data "aws_iam_policy_document" "cloudtrail_cloudwatch" {
  statement {
    effect    = "Allow"
    actions   = ["logs:CreateLogStream"]
    resources = [aws_cloudwatch_log_group.cloudtrail.arn]
  }

  statement {
    effect    = "Allow"
    actions   = ["logs:PutLogEvents"]
    resources = [aws_cloudwatch_log_group.cloudtrail.arn]
  }
}

resource "aws_cloudwatch_log_group" "cloudtrail" {
  name = "cloudtrail"

  tags = local.default_tags
}
