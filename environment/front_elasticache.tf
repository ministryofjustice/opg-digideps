resource "aws_elasticache_replication_group" "front" {
  automatic_failover_enabled    = local.account.elasticache_count == 1 ? false : true
  engine                        = "redis"
  engine_version                = "5.0.0"
  replication_group_id          = "front-rep-group-${local.environment}"
  replication_group_description = "Replication Group for Front"
  node_type                     = "cache.t2.small"
  number_cache_clusters         = local.account.elasticache_count
  parameter_group_name          = "default.redis5.0"
  port                          = 6379
  subnet_group_name             = local.account.ec_subnet_group
  security_group_ids            = [module.front_cache_security_group.id]
  tags                          = local.default_tags
  apply_immediately             = true
}
