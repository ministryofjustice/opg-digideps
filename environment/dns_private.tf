resource "aws_route53_zone" "internal" {
  name    = "${local.environment}.internal"
  comment = ""

  vpc {
    vpc_id = data.aws_vpc.vpc.id
  }
}

resource "aws_route53_record" "front_redis" {
  name    = "front-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = local.account.is_production == 0 ? [aws_elasticache_replication_group.front[0].primary_endpoint_address] : [aws_elasticache_cluster.front[0].cache_nodes[0].address]
  ttl     = 300
}

resource "aws_route53_record" "admin_redis" {
  name    = "admin-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = local.account.is_production == 0 ? [aws_elasticache_replication_group.admin[0].primary_endpoint_address] : [aws_elasticache_cluster.admin[0].cache_nodes[0].address]
  ttl     = 300
}

resource "aws_route53_record" "api_redis" {
  name    = "api-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = local.account.is_production == 0 ? [aws_elasticache_replication_group.api[0].primary_endpoint_address] : [aws_elasticache_cluster.api[0].cache_nodes[0].address]
  ttl     = 300
}
