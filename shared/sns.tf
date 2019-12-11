resource "aws_sns_topic" "alerts" {
  name = "alerts"
}

resource "aws_sns_topic" "availability-alert" {
  provider     = aws.us-east-1
  name         = "availability-alert"
  display_name = "${local.default_tags["application"]} ${local.default_tags["environment-name"]} Availability Alert"
}
