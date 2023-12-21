module "oidc_provider" {
  source = "./modules/oidc"
}

locals {
  github_oicd_issuer = "token.actions.githubusercontent.com"
  repo               = "ministryofjustice/opg-digideps"
}

resource "aws_iam_role" "oidc_role" {
  name               = "github-actions-test-role-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.github_actions_assume_role_policy.json
}

data "aws_iam_policy_document" "github_actions_assume_role_policy" {
  statement {
    sid     = "AssumeRoleWithWebId"
    actions = ["sts:AssumeRoleWithWebIdentity"]
    principals {
      type        = "Federated"
      identifiers = [module.oidc_provider.openid_connect_provider.arn]
    }

    condition {
      test     = "StringEquals"
      variable = "${local.github_oicd_issuer}:aud"
      values   = ["sts.amazonaws.com"]
    }

    condition {
      test     = "StringLike"
      variable = "${local.github_oicd_issuer}:sub"
      values   = ["repo:${local.repo}:*"]
    }
  }
}




# TF BASIC USER FOR TESTING PURPOSES
resource "aws_iam_role" "tf_basic_user" {
  name               = "tf-basic-user-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.tf_basic_user_assume_role_policy.json
}

data "aws_iam_policy_document" "tf_basic_user_assume_role_policy" {
  statement {
    sid    = "AllowAssumeFromOICDRole"
    effect = "Allow"
    principals {
      type        = "AWS"
      identifiers = [aws_iam_role.oidc_role.arn]
    }
    actions = ["sts:AssumeRole"]
  }
}

resource "aws_iam_role_policy" "tf_basic_user" {
  name   = "tf-basic-${local.environment}"
  role   = aws_iam_role.tf_basic_user.name
  policy = data.aws_iam_policy_document.tf_basic_policy.json
}

data "aws_iam_policy_document" "tf_basic_policy" {
  statement {
    sid    = "S3Allow"
    effect = "Allow"
    actions = [
      "s3:ListBucket",
      "s3:GetObject",
      "s3:PutObject",
      "s3:DeleteObject"
    ]

    resources = [
      "arn:aws:s3:::s3-access-logs.jstestsp",
      "arn:aws:s3:::s3-access-logs.jstestsp/*"
    ]
  }

  statement {
    sid       = "SNSAllow"
    effect    = "Allow"
    actions   = ["sns:*"]
    resources = ["*"]
  }

  statement {
    sid       = "DynamoAllow"
    effect    = "Allow"
    actions   = ["dynamodb:*"]
    resources = ["arn:aws:dynamodb:eu-west-1:248804316466:table/remote_lock"]
  }
}
