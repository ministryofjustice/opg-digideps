resource "aws_iam_role" "rds_iam_test" {
  assume_role_policy = data.aws_iam_policy_document.rds_iam_test_profile.json
  name               = "rds-iam-test.${local.environment}"
  tags               = local.default_tags
}

resource "aws_iam_role_policy" "rds_iam_test" {
  name   = "rds-iam-test.${local.environment}"
  policy = data.aws_iam_policy_document.rds_iam.json
  role   = aws_iam_role.rds_iam_test.id
}

#data "aws_iam_policy_document" "rds_iam_assume_role" {
#  statement {
#    effect  = "Allow"
#    actions = ["sts:AssumeRole"]
#
#    principals {
#      identifiers = [
#        "arn:aws:iam::631181914621:user/james.warren",
#      ]
#      type = "AWS"
#    }
#  }
#}

data "aws_iam_policy_document" "rds_iam" {
  statement {
    sid    = "ConnectRDSIam"
    effect = "Allow"

    actions = [
      "rds-db:connect",
    ]

    resources = ["arn:aws:rds-db:eu-west-1:248804316466:dbuser:*/*"]
  }

  statement {
    sid    = "UseCloud9"
    effect = "Allow"

    actions = [
      "cloud9:CreateEnvironmentEC2",
      "cloud9:CreateEnvironmentSSH",
      "cloud9:GetUserPublicKey",
      "cloud9:GetUserSettings",
      "cloud9:TagResource",
      "cloud9:UpdateUserSettings",
      "cloud9:ValidateEnvironmentName",
      "ec2:DescribeSubnets",
      "ec2:DescribeVpcs",
      "iam:GetUser",
      "iam:ListUsers",
    ]

    resources = ["*"]
  }

  statement {
    sid    = "ViewCloud9Environments"
    effect = "Allow"

    actions = [
      "cloud9:DescribeEnvironmentMemberships",
    ]

    resources = ["*"]

    condition {
      test     = "Null"
      values   = ["true"]
      variable = "cloud9:UserArn"
    }

    condition {
      test     = "Null"
      values   = ["true"]
      variable = "cloud9:EnvironmentId"
    }
  }

  statement {
    sid    = "CreateCloud9Role"
    effect = "Allow"

    actions = [
      "iam:CreateServiceLinkedRole",
    ]

    resources = ["*"]

    condition {
      test     = "Null"
      values   = ["true"]
      variable = "cloud9:UserArn"
    }

    condition {
      test     = "StringLike"
      values   = ["cloud9.amazonaws.com"]
      variable = "iam:AWSServiceName"
    }
  }
  statement {
    effect = "Allow"
    actions = [
      "cloud9:CreateEnvironmentEC2",
      "cloud9:CreateEnvironmentSSH"
    ]
    resources = ["*"]
    condition {
      test     = "Null"
      values   = ["true"]
      variable = "cloud9:OwnerArn"
    }
  }

  statement {
    sid       = "SSMAccessToEC2"
    effect    = "Allow"
    actions   = ["ssm:StartSession"]
    resources = ["*"]
  }
}

resource "aws_iam_instance_profile" "rds_iam_test_profile" {
  name = "migration_rds_c9_profile.${local.environment}"
  role = aws_iam_role.rds_iam_test.name
}

data "aws_iam_policy_document" "rds_iam_test_profile" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ec2.amazonaws.com"]
      type        = "Service"
    }
  }
}

#        {
#            "Effect": "Allow",
#            "Action": "ssm:StartSession",
#            "Resource": "arn:aws:ec2:*:*:instance/*",
#            "Condition": {
#                "StringLike": {
#                    "ssm:resourceTag/aws:cloud9:environment": "*"
#                },
#                "StringEquals": {
#                    "aws:CalledViaFirst": "cloud9.amazonaws.com"
#                }
#            }
#        },
#        {
#            "Effect": "Allow",
#            "Action": [
#                "ssm:StartSession"
#            ],
#            "Resource": [
#                "arn:aws:ssm:*:*:document/*"
#            ]
#        }
#    ]
#}
