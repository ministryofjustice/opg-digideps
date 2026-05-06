############################
# 1) Permissions boundary policy (managed policy)
############################

resource "aws_iam_policy" "development_ci_boundary" {
  name        = "digideps-ci-boundary"
  description = "Permission boundary for digideps CI role and any roles it creates"
  policy      = data.aws_iam_policy_document.development_ci_boundary_policy.json
}

data "aws_iam_policy_document" "development_ci_boundary_policy" {
  statement {
    sid    = "AllowSelectedServices"
    effect = "Allow"

    actions = [
      "dynamodb:*",
      "logs:*",
      "iam:*",
      "ec2:*",
      "lambda:*",
      "s3:*"
    ]

    resources = ["*"]
  }

  # Optional (recommended): explicit denies for high-risk IAM boundary tampering *by the bounded role itself*
  # This doesn't stop an admin outside the boundary, but it prevents accidental/intentional boundary removal
  # if your allow list includes iam:*.
  statement {
    sid    = "DenyBoundaryRemovalOnAnyRole"
    effect = "Deny"
    actions = [
      "iam:DeleteRolePermissionsBoundary"
    ]
    resources = ["*"]
  }
}

############################
# 2) Development CI role with boundary enabled
############################

resource "aws_iam_role" "development_ci" {
  name                 = "digideps-ci-boundary"
  assume_role_policy   = data.aws_iam_policy_document.development_ci_assume.json
  permissions_boundary = aws_iam_policy.development_ci_boundary.arn
}

data "aws_iam_policy_document" "development_ci_assume" {
  statement {
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::248804316466:role/cross-acc-db-backup.digideps-development"]
    }

    actions = [
      "sts:AssumeRole",
      "sts:TagSession"
    ]
  }
}

# You can keep this if you want “admin but capped by boundary”.
# Without a boundary it’s full admin; WITH the boundary it becomes “admin within the boundary”.
resource "aws_iam_role_policy_attachment" "development_ci_admin" {
  role       = aws_iam_role.development_ci.name
  policy_arn = "arn:aws:iam::aws:policy/AdministratorAccess"
}

############################
# 3) Guardrails policy attached to CI role (self-protection + boundary enforcement)
############################

resource "aws_iam_role_policy" "development_ci_guardrails" {
  name   = "digideps-ci-guardrails"
  role   = aws_iam_role.development_ci.id
  policy = data.aws_iam_policy_document.development_ci_guardrails.json
}

data "aws_iam_policy_document" "development_ci_guardrails" {

  # 3a) Stop CI from modifying itself
  statement {
    sid    = "StopEditingSelf"
    effect = "Deny"

    actions = [
      "iam:AttachRolePolicy",
      "iam:DetachRolePolicy",
      "iam:PutRolePolicy",
      "iam:DeleteRolePolicy",
      "iam:UpdateAssumeRolePolicy",
      "iam:PutRolePermissionsBoundary",
      "iam:DeleteRolePermissionsBoundary",
      "iam:DeleteRole",
      "iam:TagRole",
      "iam:UntagRole"
    ]

    resources = [aws_iam_role.development_ci.arn]
  }

  # 3b) Protect the boundary policy itself (prevent version/changes)
  statement {
    sid    = "ProtectBoundaryPolicy"
    effect = "Deny"

    actions = [
      "iam:CreatePolicyVersion",
      "iam:SetDefaultPolicyVersion",
      "iam:DeletePolicy",
      "iam:DeletePolicyVersion"
    ]

    resources = [aws_iam_policy.development_ci_boundary.arn]
  }

  # 3c) Enforce: any role CI creates MUST have this boundary
  statement {
    sid    = "RequireBoundaryOnCreateRole"
    effect = "Deny"

    actions   = ["iam:CreateRole"]
    resources = ["*"]

    condition {
      test     = "StringNotEquals"
      variable = "iam:PermissionsBoundary"
      values   = [aws_iam_policy.development_ci_boundary.arn]
    }
  }

  # 3d) Enforce: CI can only set a role boundary to THIS boundary (prevents “set none” or “set different”)
  statement {
    sid    = "RequireBoundaryOnPutRolePermissionsBoundary"
    effect = "Deny"

    actions   = ["iam:PutRolePermissionsBoundary"]
    resources = ["*"]

    condition {
      test     = "StringNotEquals"
      variable = "iam:PermissionsBoundary"
      values   = [aws_iam_policy.development_ci_boundary.arn]
    }
  }

  # Optional but useful: prevent CI from ever removing a boundary from roles
  statement {
    sid       = "DenyBoundaryRemoval"
    effect    = "Deny"
    actions   = ["iam:DeleteRolePermissionsBoundary"]
    resources = ["*"]
  }
}
