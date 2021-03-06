resource "aws_route53_zone" "internal" {
  name    = "${local.environment}.internal"
  comment = "Private Route53 Zone for ${local.environment}"

  vpc {
    vpc_id = data.aws_vpc.vpc.id
  }

  tags = local.default_tags
}

resource "aws_route53_record" "frontend_redis" {
  name    = "frontend-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_replication_group.frontend.primary_endpoint_address]
  ttl     = 300
}

resource "aws_route53_record" "api_redis" {
  name    = "api-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_replication_group.api.primary_endpoint_address]
  ttl     = 300
}
