locals {
  s3_alb_log_account_names = {
    "development" : "dev",
    "preproduction" : "pre",
    "production" : "prod",
  }
  s3_alb_log_account_name    = local.s3_alb_log_account_names[var.account.name]
  s3_access_log_account_name = var.account.name == "development" ? "dev" : var.account.name
}

resource "aws_s3_bucket" "alb_access" {
  bucket        = "alb-logs.${data.aws_region.current.name}.${local.s3_alb_log_account_name}.digideps.opg.service.justice.gov.uk"
  force_destroy = var.account.name == "production" ? false : true
}

resource "aws_s3_bucket_ownership_controls" "alb_access" {
  bucket = aws_s3_bucket.alb_access.id
  rule {
    object_ownership = "BucketOwnerEnforced"
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "alb_access" {
  bucket = aws_s3_bucket.alb_access.id

  rule {
    id     = "ExpireObjectsAfter13Months"
    status = "Enabled"

    filter {
      prefix = ""
    }
    expiration {
      days = 400
    }
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "alb_access" {
  bucket = aws_s3_bucket.alb_access.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_public_access_block" "alb_access" {
  bucket = aws_s3_bucket.alb_access.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_policy" "alb_access" {
  depends_on = [aws_s3_bucket_public_access_block.alb_access]
  bucket     = aws_s3_bucket.alb_access.id
  policy     = data.aws_iam_policy_document.alb_access.json
}

data "aws_s3_bucket" "access_logging" {
  bucket = "s3-access-logs-opg-digideps-${local.s3_access_log_account_name}-${data.aws_region.current.name}"
}

resource "aws_s3_bucket_logging" "alb_access" {
  bucket = aws_s3_bucket.alb_access.id

  target_bucket = data.aws_s3_bucket.access_logging.id
  target_prefix = "log/${aws_s3_bucket.alb_access.id}/"
}

data "aws_elb_service_account" "region" {}

data "aws_iam_policy_document" "alb_access" {
  policy_id = "PutObjPolicy"

  statement {
    sid    = "AllowALBAccountPutAccess"
    effect = "Allow"
    principals {
      identifiers = [data.aws_elb_service_account.region.arn]
      type        = "AWS"
    }
    actions = ["s3:PutObject"]
    resources = [
      aws_s3_bucket.alb_access.arn,
      "${aws_s3_bucket.alb_access.arn}/*"
    ]
  }

  statement {
    sid    = "AllowLogDeliveryPutAccess"
    effect = "Allow"
    principals {
      identifiers = ["delivery.logs.amazonaws.com"]
      type        = "Service"
    }
    actions = ["s3:PutObject"]
    resources = [
      aws_s3_bucket.alb_access.arn,
      "${aws_s3_bucket.alb_access.arn}/*"
    ]
    condition {
      test     = "StringEquals"
      variable = "s3:x-amz-acl"
      values   = ["bucket-owner-full-control"]
    }
  }

  statement {
    sid    = "AllowLogDeliveryGetAcl"
    effect = "Allow"
    principals {
      identifiers = ["delivery.logs.amazonaws.com"]
      type        = "Service"
    }
    actions   = ["s3:GetBucketAcl"]
    resources = [aws_s3_bucket.alb_access.arn]
  }

  statement {
    sid    = "DenyNoneSSLRequests"
    effect = "Deny"
    principals {
      type        = "AWS"
      identifiers = ["*"]
    }
    actions = ["s3:*"]
    resources = [
      aws_s3_bucket.alb_access.arn,
      "${aws_s3_bucket.alb_access.arn}/*"
    ]
    condition {
      test     = "Bool"
      variable = "aws:SecureTransport"
      values   = [false]
    }
  }
}
