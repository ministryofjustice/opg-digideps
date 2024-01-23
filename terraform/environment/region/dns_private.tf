resource "aws_route53_zone" "internal" {
  name    = "${local.environment}.internal"
  comment = "Private Route53 Zone for ${local.environment}"

  vpc {
    vpc_id = data.aws_vpc.vpc.id
  }

  tags = var.default_tags
}

resource "aws_route53_record" "frontend_redis" {
  name    = "frontend-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [data.aws_elasticache_replication_group.front_cache_cluster.primary_endpoint_address]
  ttl     = 300
}

resource "aws_route53_record" "api_redis" {
  name    = "api-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [data.aws_elasticache_replication_group.api_cache_cluster.primary_endpoint_address]
  ttl     = 300
}
