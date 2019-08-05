#TODO: this needed to be in complete-deputy-report.service.gov.uk
resource "aws_route53_record" "admin" {
  name    = "${local.admin_prefix}${local.host_suffix}"
  type    = "A"
  zone_id = data.aws_route53_zone.public.id

  alias {
    evaluate_target_health = false
    name                   = aws_lb.admin.dns_name
    zone_id                = aws_lb.admin.zone_id
  }
}

resource "aws_route53_record" "admin_redis" {
  name    = "admin-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_cluster.admin.cache_nodes[0].address]
  ttl     = 300
}

