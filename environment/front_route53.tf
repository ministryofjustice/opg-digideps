resource "aws_route53_record" "front" {
  name    = "${local.front_prefix}${local.host_suffix}"
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = aws_lb.front.dns_name
    zone_id                = aws_lb.front.zone_id
  }
}

resource "aws_route53_record" "front_redis" {
  name    = "front-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_cluster.front.cache_nodes[0].address]
  ttl     = 300
}

