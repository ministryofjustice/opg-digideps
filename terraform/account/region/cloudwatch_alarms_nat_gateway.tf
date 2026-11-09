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
  alarm_description = "BytesOutToDestination for NAT Gateway ${each.key}"

  actions_enabled = var.account.alarms_active
  alarm_actions   = [aws_sns_topic.alerts.arn]
  ok_actions      = var.account.pagerduty_enabled ? [aws_sns_topic.alerts.arn] : []

  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = 1
  datapoints_to_alarm = 1
  threshold           = 0

  treat_missing_data = "notBreaching"

  metric_query {
    id          = "nat_bytes"
    return_data = false

    metric {
      namespace   = "AWS/NATGateway"
      metric_name = "BytesOutToDestination"
      stat        = "Maximum"
      period      = 60

      dimensions = {
        NatGatewayId = each.key
      }
    }
  }

  metric_query {
    id          = "database_activity"
    return_data = false

    metric {
      namespace   = "DigiDeps/Database"
      metric_name = "DatabaseActivityInProgress.${var.account.primary_workspace}"
      period      = 60
      stat        = "Maximum"
    }
  }

  metric_query {
    id = "alarm_condition"

    expression = "IF(database_activity > 0, nat_bytes - 200000, nat_bytes - 100000)"

    label       = "Conditional NAT threshold"
    return_data = true
  }

  tags = var.default_tags
}
