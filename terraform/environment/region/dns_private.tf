resource "aws_route53_zone" "internal" {
  name    = "${local.environment}.internal"
  comment = "Private Route53 Zone for ${local.environment}"

  vpc {
    vpc_id = var.account.use_new_network ? data.aws_vpc.main[0].id : data.aws_vpc.vpc.id
  }

  tags = var.default_tags
}

locals {
  front_redis = var.account.use_new_network ? data.aws_elasticache_replication_group.front_redis_cluster[0] : data.aws_elasticache_replication_group.front_cache_cluster
  api_redis   = var.account.use_new_network ? data.aws_elasticache_replication_group.api_redis_cluster[0] : data.aws_elasticache_replication_group.api_cache_cluster
}

resource "aws_route53_record" "frontend_redis" {
  name    = "frontend-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.front_redis.primary_endpoint_address]
  ttl     = 300
}

resource "aws_route53_record" "api_redis" {
  name    = "api-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [local.api_redis.primary_endpoint_address]
  ttl     = 300
}
