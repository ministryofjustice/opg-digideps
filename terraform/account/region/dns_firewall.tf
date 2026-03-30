resource "aws_cloudwatch_log_group" "aws_route53_resolver_query_log" {
  count             = var.account.resolver_logs_enabled ? 1 : 0
  name              = "digideps-aws-route53-resolver-query-log-config"
  retention_in_days = 180
  kms_key_id        = module.logs_kms.eu_west_1_target_key_arn
  tags = {
    "Name" = "digideps-aws-route53-resolver-query-log-config"
  }
}

resource "aws_cloudwatch_log_anomaly_detector" "aws_route53_resolver_query_log" {
  count                   = var.account.resolver_logs_enabled ? 1 : 0
  detector_name           = "aws-route53-resolver-query"
  log_group_arn_list      = [aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].arn]
  anomaly_visibility_time = 14
  evaluation_frequency    = "TEN_MIN"
  enabled                 = "true"
  kms_key_id              = module.anomaly_kms.eu_west_1_target_key_arn
}

resource "aws_route53_resolver_query_log_config" "egress" {
  count           = var.account.resolver_logs_enabled ? 1 : 0
  name            = "egress"
  destination_arn = aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].arn
}

resource "aws_route53_resolver_query_log_config_association" "egress" {
  count                        = var.account.resolver_logs_enabled ? 1 : 0
  resolver_query_log_config_id = aws_route53_resolver_query_log_config.egress[0].id
  resource_id                  = module.network.vpc.id
}
