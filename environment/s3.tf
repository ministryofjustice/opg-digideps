resource "aws_s3_bucket" "pa_uploads" {
  bucket        = "pa-uploads-${terraform.workspace}"
  acl           = ""
  force_destroy = true

  lifecycle_rule {
    enabled = true

    expiration {
      days = 400
    }
  }

  tags = "${local.default_tags}"
}

resource "aws_s3_bucket_policy" "pa_uploads" {
  bucket = "${aws_s3_bucket.pa_uploads.id}"
  policy = "${data.aws_iam_policy_document.pa_uploads.json}"
}

data "aws_iam_policy_document" "pa_uploads" {
  policy_id = "PutObjPolicy"

  statement {
    sid    = "DenyUnEncryptedObjectUploads"
    effect = "Deny"

    principals {
      identifiers = ["*"]
      type        = "AWS"
    }

    actions   = ["s3:PutObject"]
    resources = ["${aws_s3_bucket.pa_uploads.arn}/*"]

    condition {
      test     = "StringNotEquals"
      values   = ["AES256"]
      variable = "s3:x-amz-server-side-encryption"
    }
  }
}

data "aws_iam_policy_document" "s3_uploads_readdelete" {
  statement {
    effect = "Allow"

    actions = [
      "s3:GetObject",
      "s3:DeleteObject",
      "s3:PutObjectTagging",
      "s3:GetObjectTagging",
    ]

    resources = ["${aws_s3_bucket.pa_uploads.arn}/*"]
  }
}

data "aws_iam_policy_document" "s3_uploads_writeonly" {
  statement {
    effect = "Allow"

    actions = [
      "s3:PutObject",
      "s3:PutObjectTagging",
    ]

    resources = ["${aws_s3_bucket.pa_uploads.arn}/*"]
  }
}

resource "aws_s3_bucket" "backup" {
  bucket = "${join(".",compact(list("backup", terraform.workspace, local.account_name, local.domain_name )))}"
  acl    = ""
  tags   = "${local.default_tags}"
}

data "aws_iam_policy_document" "s3_backups" {
  statement {
    effect = "Allow"

    actions = [
      "s3:PutObject",
      "s3:GetObject",
      "s3:DeleteObject",
      "s3:ListObject",
    ]

    resources = ["${aws_s3_bucket.backup.arn}*"]
  }

  statement {
    effect = "Allow"

    actions = [
      "s3:ListObject",
      "s3:ListBucket",
      "s3:GetObject",
    ]

    resources = ["${aws_s3_bucket.backup.arn}"]
  }
}
