resource "aws_cloudtrail" "cloudtrail" {
  name           = "cloudtrail"
  s3_bucket_name = aws_s3_bucket.cloudtrail.bucket

  cloud_watch_logs_group_arn = aws_cloudwatch_log_group.cloudtrail.arn
  cloud_watch_logs_role_arn  = aws_iam_role.cloudtrail_cloudwatch.arn

  enable_log_file_validation = true
  is_multi_region_trail      = true

  event_selector {
    include_management_events = true
    read_write_type           = "All"

    data_resource {
      type   = "AWS::S3::Object"
      values = ["arn:aws:s3"]
    }

    data_resource {
      type   = "AWS::Lambda::Function"
      values = ["arn:aws:lambda"]
    }
  }

  tags = local.default_tags
}
