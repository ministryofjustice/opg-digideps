resource "aws_iam_role" "ci_test" {
  assume_role_policy = data.aws_iam_policy_document.ci_test_assume_policy.json
  name               = "digideps-test-ci"
  tags               = var.default_tags
  provider           = aws.management
}

data "aws_iam_policy_document" "ci_test_assume_policy" {
  statement {
    sid    = "Trust relations"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::631181914621:role/oidc-digideps-development"]
    }

    actions = ["sts:AssumeRole"]
  }
}

resource "aws_iam_role_policy" "ci_test" {
  name     = "digideps-test-ci"
  policy   = data.aws_iam_policy_document.ci_test.json
  role     = aws_iam_role.ci_test.id
  provider = aws.management
}

data "aws_iam_policy_document" "ci_test" {
  statement {
    sid    = "Allow digideps DNS access"
    effect = "Allow"
    actions = [
      "route53:ChangeResourceRecordSets",
      "route53:GetHostedZone",
      "route53:ListResourceRecordSets"
    ]
    resources = [
      "arn:aws:route53:::hostedzone/Z07336273PD3FH7YGYOLV",
      "arn:aws:route53:::hostedzone/Z0818402Q9ADP2GK9BOL"
    ]
  }

  statement {
    sid    = "ECR Allow List"
    effect = "Allow"
    actions = [
      "ecr:List*",
      "ecr:Describe*"
    ]
    resources = ["*"]
  }

  statement {
    sid    = "ECR Allow Read"
    effect = "Allow"
    actions = [
      "ecr:GetAuthorizationToken",
      "ecr:BatchCheckLayerAvailability",
      "ecr:GetDownloadUrlForLayer",
      "ecr:GetRepositoryPolicy",
      "ecr:DescribeRepositories",
      "ecr:ListImages",
      "ecr:DescribeImages",
      "ecr:BatchGetImage",
      "ecr:GetLifecyclePolicy",
      "ecr:GetLifecyclePolicyPreview",
      "ecr:ListTagsForResource",
      "ecr:DescribeImageScanFindings"
    ]
    resources = [
      "311462405659.dkr.ecr.eu-west-1.amazonaws.com/digideps/*"
    ]
  }

  statement {
    sid    = "GetSecrets"
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue",
    ]
    resources = [
      "arn:aws:secretsmanager:eu-west-1:311462405659:secret:digideps/*"
    ]
  }
}
