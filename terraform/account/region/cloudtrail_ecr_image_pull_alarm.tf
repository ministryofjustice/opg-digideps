data "aws_cloudwatch_log_group" "cloudtrail" {
  name = "cloudtrail"
}

resource "aws_cloudwatch_log_metric_filter" "ecr_image_not_found" {
  name           = "ECRImageNotFound.${var.account.name}"
  log_group_name = data.aws_cloudwatch_log_group.cloudtrail.name
  pattern        = "{ ($.eventSource = \"ecr.amazonaws.com\") && ($.eventName = \"BatchGetImage\") && ($.responseElements.failures[*].failureCode = \"ImageNotFound\") }"

  metric_transformation {
    name          = "ECRImageNotFound.${var.account.name}"
    namespace     = "DigiDeps/Deployment"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "ecr_image_not_found" {
  alarm_name          = "cloudtrail-${var.account.name}-ecr-image-not-found"
  alarm_description   = "Triggers when an ECS deployment or redeployment attempts to pull an ECR image tag that does not exist."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 1
  datapoints_to_alarm = 1
  threshold           = 1
  period              = 60
  statistic           = "Sum"

  namespace   = aws_cloudwatch_log_metric_filter.ecr_image_not_found.metric_transformation[0].namespace
  metric_name = aws_cloudwatch_log_metric_filter.ecr_image_not_found.metric_transformation[0].name

  alarm_actions      = [aws_sns_topic.alerts.arn]
  treat_missing_data = "notBreaching"
  actions_enabled    = true

  tags = var.default_tags
}

resource "aws_cloudwatch_query_definition" "ecr_image_not_found" {
  name            = "Deployment/ECR Image Not Found"
  log_group_names = [data.aws_cloudwatch_log_group.cloudtrail.name]

  query_string = <<EOF
fields @timestamp, eventSource, eventName, errorCode, requestParameters.repositoryName, requestParameters.imageIds, responseElements.failures
| filter eventSource = "ecr.amazonaws.com"
| filter eventName = "BatchGetImage"
| filter responseElements.failures like "ImageNotFound"
| sort @timestamp desc
EOF
}
