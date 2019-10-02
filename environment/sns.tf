resource "aws_sns_topic" "notifications_info" {
  name = "notifications-info-${local.environment}"
}

resource "aws_sns_topic" "notifications_minor" {
  name = "notifications-minor-${local.environment}"
}

resource "aws_sns_topic_policy" "notifications_minor" {
  arn    = aws_sns_topic.notifications_minor.arn
  policy = data.aws_iam_policy_document.notifications_minor.json
}

data "aws_sns_topic" "alerts" {
  name = "alerts"
}

data "aws_iam_policy_document" "notifications_minor" {
  policy_id = "__default_policy_ID"

  statement {
    sid    = "__default_statement_ID"
    effect = "Allow"

    principals {
      identifiers = ["*"]
      type        = "AWS"
    }

    actions = [
      "SNS:GetTopicAttributes",
      "SNS:SetTopicAttributes",
      "SNS:AddPermission",
      "SNS:RemovePermission",
      "SNS:DeleteTopic",
      "SNS:Subscribe",
      "SNS:ListSubscriptionsByTopic",
      "SNS:Publish",
      "SNS:Receive",
    ]

    resources = [aws_sns_topic.notifications_minor.arn]

    condition {
      test     = "StringEquals"
      values   = ["248804316466"]
      variable = "AWS:SourceOwner"
    }
  }
}

resource "aws_sns_topic" "notifications_major" {
  name = "notifications-major-${local.environment}"
}

resource "aws_sns_topic_policy" "notifications_major" {
  arn    = aws_sns_topic.notifications_major.arn
  policy = data.aws_iam_policy_document.notifications_major.json
}

data "aws_iam_policy_document" "notifications_major" {
  policy_id = "__default_policy_ID"

  statement {
    sid    = "__default_statement_ID"
    effect = "Allow"

    principals {
      identifiers = ["*"]
      type        = "AWS"
    }

    actions = [
      "SNS:GetTopicAttributes",
      "SNS:SetTopicAttributes",
      "SNS:AddPermission",
      "SNS:RemovePermission",
      "SNS:DeleteTopic",
      "SNS:Subscribe",
      "SNS:ListSubscriptionsByTopic",
      "SNS:Publish",
      "SNS:Receive",
    ]

    resources = [aws_sns_topic.notifications_major.arn]

    condition {
      test     = "StringEquals"
      values   = ["248804316466"]
      variable = "AWS:SourceOwner"
    }
  }
}
