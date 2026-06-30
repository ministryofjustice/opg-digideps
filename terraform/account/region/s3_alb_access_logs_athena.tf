resource "aws_s3_bucket" "alb_access_athena_results" {
  bucket        = "alb-athena.${data.aws_region.current.name}.${local.s3_access_log_account_name}.digideps.opg.justice.gov.uk"
  force_destroy = var.account.name == "production" ? false : true
}

resource "aws_s3_bucket_ownership_controls" "alb_access_athena_results" {
  bucket = aws_s3_bucket.alb_access_athena_results.id
  rule {
    object_ownership = "BucketOwnerEnforced"
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "alb_access_athena_results" {
  bucket = aws_s3_bucket.alb_access_athena_results.id

  rule {
    id     = "ExpireObjectsAfter28Days"
    status = "Enabled"

    filter {
      prefix = ""
    }

    expiration {
      days = 28
    }
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "alb_access_athena_results" {
  bucket = aws_s3_bucket.alb_access_athena_results.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_public_access_block" "alb_access_athena_results" {
  bucket = aws_s3_bucket.alb_access_athena_results.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_policy" "alb_access_athena_results" {
  depends_on = [aws_s3_bucket_public_access_block.alb_access_athena_results]
  bucket     = aws_s3_bucket.alb_access_athena_results.id
  policy     = data.aws_iam_policy_document.alb_access_athena_results.json
}

resource "aws_s3_bucket_logging" "alb_access_athena_results" {
  bucket = aws_s3_bucket.alb_access_athena_results.id

  target_bucket = data.aws_s3_bucket.access_logging.id
  target_prefix = "log/${aws_s3_bucket.alb_access_athena_results.id}/"
}

data "aws_iam_policy_document" "alb_access_athena_results" {
  policy_id = "PutObjPolicy"

  statement {
    sid     = "DenyNoneSSLRequests"
    effect  = "Deny"
    actions = ["s3:*"]
    resources = [
      aws_s3_bucket.alb_access_athena_results.arn,
      "${aws_s3_bucket.alb_access_athena_results.arn}/*"
    ]

    condition {
      test     = "Bool"
      variable = "aws:SecureTransport"
      values   = [false]
    }

    principals {
      type        = "AWS"
      identifiers = ["*"]
    }
  }

  statement {
    sid     = "AllowOperatorAccess"
    effect  = "Allow"
    actions = ["s3:*"]
    resources = [
      aws_s3_bucket.alb_access_athena_results.arn,
      "${aws_s3_bucket.alb_access_athena_results.arn}/*"
    ]

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/operator"]
    }
  }
}

resource "aws_athena_workgroup" "alb_logs" {
  name          = "${var.account.name}-${data.aws_region.current.name}"
  description   = "Workgroup for the interrogation of Load Balancer Logs in ${var.account.name} ${data.aws_region.current.name}"
  force_destroy = var.account.name == "production" ? false : true

  configuration {
    enforce_workgroup_configuration    = true
    publish_cloudwatch_metrics_enabled = true

    result_configuration {
      output_location = "s3://${aws_s3_bucket.alb_access_athena_results.bucket}/workspace/"

      encryption_configuration {
        encryption_option = "SSE_S3"
      }
    }
  }
}

resource "aws_athena_database" "access_logs" {
  name          = "${var.account.name}_load_balancer_logs"
  bucket        = aws_s3_bucket.alb_access_athena_results.id
  force_destroy = var.account.name == "production" ? false : true

  encryption_configuration {
    encryption_option = "SSE_S3"
  }
}

resource "aws_athena_named_query" "alb_errors" {
  name        = "alb-errors"
  description = "All 4xx and 5xx alb errors excluding 400"
  workgroup   = aws_athena_workgroup.alb_logs.id
  database    = aws_athena_database.access_logs.name
  query       = templatefile("${path.module}/alb_errors.tpl", {})
}
