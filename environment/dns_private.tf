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
  records = [aws_elasticache_cluster.front.cache_nodes[0].address]
  ttl     = 300
}

resource "aws_route53_record" "admin_redis" {
  name    = "admin-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_cluster.admin.cache_nodes[0].address]
  ttl     = 300
}
