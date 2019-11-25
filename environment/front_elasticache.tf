resource "aws_elasticache_cluster" "front" {
  cluster_id           = "front-${local.environment}"
  engine               = "redis"
  node_type            = "cache.t2.small"
  num_cache_nodes      = 1
  parameter_group_name = "default.redis5.0"
  engine_version       = "5.0.0"
  port                 = 6379
  subnet_group_name    = local.account.ec_subnet_group
  security_group_ids   = [module.front_cache_security_group.id]
  tags                 = local.default_tags
}
