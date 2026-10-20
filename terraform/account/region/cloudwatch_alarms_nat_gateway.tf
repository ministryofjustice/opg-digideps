data "aws_nat_gateways" "main" {
  vpc_id = module.network.vpc.id

  filter {
    name   = "state"
    values = ["available"]
  }
}

resource "aws_cloudwatch_metric_alarm" "nat_gateway_bytes_out" {
  for_each = toset(data.aws_nat_gateways.main.ids)

  alarm_name        = "${var.account.name}-${each.key}-nat-bytes-out"
  alarm_description = "BytesOutToDestination for NAT Gateway ${each.key}."

  actions_enabled = var.account.environment.alarms_active
  alarm_actions   = [aws_sns_topic.alerts.arn]
  ok_actions      = var.account.pagerduty_enabled ? [aws_sns_topic.alerts.arn] : []

  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 5
  datapoints_to_alarm = 5
  threshold           = 40000
  period              = 60

  namespace   = "AWS/NATGateway"
  metric_name = "BytesOutToDestination"
  statistic   = "Sum"

  dimensions = {
    NatGatewayId = each.key
  }

  treat_missing_data = "notBreaching"
  tags               = var.default_tags
}
