resource "aws_elasticache_cluster" "api" {
  cluster_id           = "api-${local.environment}"
  engine               = "redis"
  node_type            = "cache.t2.small"
  num_cache_nodes      = 1
  parameter_group_name = "default.redis5.0"
  engine_version       = "5.0.0"
  port                 = 6379
  subnet_group_name    = local.account.ec_subnet_group

  security_group_ids = [module.api_cache_security_group.id]

  tags = {
    InstanceName = "api-${local.environment}"
    Stack        = local.environment
  }
}

resource "aws_route53_record" "api_redis" {
  name    = "api-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_cluster.api.cache_nodes[0].address]
  ttl     = 300
}
