resource "aws_route53_resolver_query_log_config_association" "resolver" {
  resolver_query_log_config_id = aws_route53_resolver_query_log_config.resolver.id
  resource_id                  = data.aws_vpc.vpc.id
}

resource "aws_route53_resolver_query_log_config" "resolver" {
  name            = "resolver-${local.environment}"
  destination_arn = aws_cloudwatch_log_group.route53_resolver_public.arn
  tags            = local.default_tags
}

resource "aws_cloudwatch_log_group" "route53_resolver_public" {
  name              = "/aws/route53/resolver-${local.environment}"
  retention_in_days = 30
}
