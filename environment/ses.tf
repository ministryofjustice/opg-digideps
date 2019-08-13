resource "aws_iam_user" "ses" {
  name = "${local.environment}-ses"
}

resource "aws_iam_access_key" "ses" {
  user = aws_iam_user.ses.name
}

resource "aws_iam_user_policy" "ses" {
  policy = data.aws_iam_policy_document.ses.json
  user   = aws_iam_user.ses.name
}

data "aws_iam_policy_document" "ses" {
  statement {
    effect    = "Allow"
    actions   = ["ses:SendRawEmail"]
    resources = ["*"]
  }
}
