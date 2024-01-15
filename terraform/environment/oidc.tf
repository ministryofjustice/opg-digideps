module "oidc_provider" {
  source = "./modules/oidc"
}

locals {
  github_oicd_issuer = "token.actions.githubusercontent.com"
  repo               = "ministryofjustice/opg-digideps"
}

resource "aws_iam_role" "oidc_role" {
  name                 = "github-actions-test-role-${local.environment}"
  assume_role_policy   = data.aws_iam_policy_document.github_actions_assume_role_policy.json
  max_session_duration = 14400
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

data "aws_iam_policy_document" "oidc_assume_other" {
  statement {
    sid     = "StsAssume"
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    resources = [
      aws_iam_role.tf_basic_user.arn,
      aws_iam_role.tf_basic_user_sb.arn
    ]
  }
}

resource "aws_iam_role_policy" "oidc_policy" {
  name   = "oidc-basic-${local.environment}"
  role   = aws_iam_role.oidc_role.name
  policy = data.aws_iam_policy_document.oidc_assume_other.json
}

# TF BASIC USER FOR TESTING PURPOSES
resource "aws_iam_role" "tf_basic_user" {
  name                 = "tf-basic-user-${local.environment}"
  max_session_duration = 14400
  assume_role_policy   = data.aws_iam_policy_document.tf_basic_user_assume_role_policy.json
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
    sid    = "S3AllowCreate"
    effect = "Allow"
    actions = [
      "s3:CreateBucket",
      "s3:GetBucketLocation",
      "s3:ListAllMyBuckets",
      "s3:PutBucketVersioning"
    ]
    resources = ["*"]
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

# TF BASIC USER IN SANDBOX FOR TESTING PURPOSES
resource "aws_iam_role" "tf_basic_user_sb" {
  provider           = aws.sandbox
  name               = "tf-basic-user-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.tf_basic_user_assume_role_policy_sb.json
}

data "aws_iam_policy_document" "tf_basic_user_assume_role_policy_sb" {
  provider = aws.sandbox
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

resource "aws_iam_role_policy" "tf_basic_user_sb" {
  provider = aws.sandbox
  name     = "tf-basic-${local.environment}"
  role     = aws_iam_role.tf_basic_user_sb.name
  policy   = data.aws_iam_policy_document.tf_basic_policy_sb.json
}

data "aws_iam_policy_document" "tf_basic_policy_sb" {
  provider = aws.sandbox
  statement {
    sid       = "SNSAllow"
    effect    = "Allow"
    actions   = ["sns:*"]
    resources = ["*"]
  }
}
