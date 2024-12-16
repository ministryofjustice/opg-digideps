resource "aws_iam_role" "ci_test" {
  assume_role_policy = data.aws_iam_policy_document.ci_test_assume_policy.json
  name               = "digideps-test-ci"
  tags               = var.default_tags
  provider           = aws.management_admin
}

data "aws_iam_policy_document" "ci_test_assume_policy" {
  statement {
    sid    = "TrustRelations"
    effect = "Allow"

    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::631181914621:role/oidc-digideps-development"
      ]
    }

    actions = ["sts:AssumeRole"]
  }
}

resource "aws_iam_role_policy" "ci_test" {
  name     = "digideps-test-ci"
  policy   = data.aws_iam_policy_document.ci_test.json
  role     = aws_iam_role.ci_test.id
  provider = aws.management_admin
}

data "aws_iam_policy_document" "ci_test" {
  statement {
    sid    = "WriteDNSAccess"
    effect = "Allow"
    actions = [
      "route53:Change*",
      "route53:Create*",
      "route53:Delete*",
      "route53:Associate*",
      "route53:Activate*",
      "route53:Deactivate*",
    ]
    resources = [
      "arn:aws:route53:::hostedzone/Z07336273PD3FH7YGYOLV",
      "arn:aws:route53:::hostedzone/Z0818402Q9ADP2GK9BOL"
    ]
  }

  statement {
    sid    = "GetParameters"
    effect = "Allow"
    actions = [
      "ssm:GetParameter"
    ]
    resources = [
      "arn:aws:ssm:eu-west-1:311462405659:parameter/digideps/*"
    ]
  }


  statement {
    sid    = "ReadDNS"
    effect = "Allow"
    actions = [
      "route53:Get*",
      "route53:List*"
    ]
    resources = [
      "*"
    ]
  }

  statement {
    sid    = "ECRAllowList"
    effect = "Allow"
    actions = [
      "ecr:List*",
      "ecr:Describe*"
    ]
    resources = ["*"]
  }

  statement {
    sid    = "S3AllowReadWrite"
    effect = "Allow"
    actions = [
      "s3:*"
    ]
    resources = [
      "arn:aws:s3:::backup.complete-deputy-report.service.gov.uk",
      "arn:aws:s3:::backup.complete-deputy-report.service.gov.uk/*"
    ]
  }

  statement {
    sid    = "KMSListDescribe"
    effect = "Allow"
    actions = [
      "kms:ListAliases",
      "kms:DescribeKey"
    ]
    resources = ["*"]
  }

  statement {
    sid    = "ECRAllowRead"
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
      "arn:aws:ecr:eu-west-1:311462405659:digideps/*"
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
