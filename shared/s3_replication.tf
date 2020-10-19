resource "aws_s3_bucket" "pa_uploads_branch_replication" {
  count         = local.account.name == "development" ? 1 : 0
  bucket        = "pa-uploads-branch-replication"
  acl           = "private"
  force_destroy = true

  versioning {
    enabled = true
  }

  lifecycle_rule {
    enabled = true

    expiration {
      days = 10
    }

    noncurrent_version_expiration {
      days = 10
    }
  }

  server_side_encryption_configuration {
    rule {
      apply_server_side_encryption_by_default {
        sse_algorithm = "aws:kms"
      }
    }
  }

  tags = local.default_tags
}

resource "aws_s3_bucket_public_access_block" "pa_uploads_branch_replication" {
  count  = local.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket.pa_uploads_branch_replication[0].bucket

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_policy" "pa_uploads_branch_replication" {
  count  = local.account.name == "development" ? 1 : 0
  bucket = aws_s3_bucket_public_access_block.pa_uploads_branch_replication[0].bucket
  policy = data.aws_iam_policy_document.pa_uploads_branch_replication[0].json
}

data "aws_iam_policy_document" "pa_uploads_branch_replication" {
  count     = local.account.name == "development" ? 1 : 0
  policy_id = "PutObjPolicy"

  statement {
    sid    = "DenyUnEncryptedObjectUploads"
    effect = "Deny"

    principals {
      identifiers = ["*"]
      type        = "AWS"
    }

    actions   = ["s3:PutObject"]
    resources = ["${aws_s3_bucket.pa_uploads_branch_replication[0].arn}/*"]

    condition {
      test     = "StringNotEquals"
      values   = ["AES256"]
      variable = "s3:x-amz-server-side-encryption"
    }
  }
}

resource "aws_iam_role" "replication" {
  count = local.account.name == "development" ? 1 : 0
  name  = "replication-role.replication"

  assume_role_policy = <<POLICY
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": "sts:AssumeRole",
      "Principal": {
        "Service": "s3.amazonaws.com"
      },
      "Effect": "Allow",
      "Sid": ""
    }
  ]
}
POLICY
  tags = merge(
    local.default_tags,
    { Name = "replication-role-${local.account.name}" },
  )
}

resource "aws_iam_policy" "replication" {
  count = local.account.name == "development" ? 1 : 0
  name  = "replication-policy.replication"

  policy = <<POLICY
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "s3:GetReplicationConfiguration",
        "s3:ListBucket"
      ],
      "Effect": "Allow",
      "Resource": [
        "${aws_s3_bucket.pa_uploads_branch_replication[0].arn}"
      ]
    },
    {
      "Action": [
        "s3:GetObjectVersion",
        "s3:GetObjectVersionAcl"
      ],
      "Effect": "Allow",
      "Resource": [
        "${aws_s3_bucket.pa_uploads_branch_replication[0].arn}/*"
      ]
    }
  ]
}
POLICY
}

resource "aws_iam_role_policy_attachment" "replication" {
  count      = local.account.name == "development" ? 1 : 0
  role       = aws_iam_role.replication[0].name
  policy_arn = aws_iam_policy.replication[0].arn
}
