resource "aws_elasticache_replication_group" "api" {
  automatic_failover_enabled    = true
  engine                        = "redis"
  engine_version                = "5.0.0"
  availability_zones            = ["eu-west-1a", "eu-west-1b"]
  replication_group_id          = "api-rep-group-${local.environment}"
  replication_group_description = "Replication Group for API"
  node_type                     = "cache.t2.small"
  number_cache_clusters         = 0
  parameter_group_name          = "default.redis5.0"
  port                          = 6379
  subnet_group_name             = local.account.ec_subnet_group
  security_group_ids            = [module.admin_cache_security_group.id]
  tags                          = local.default_tags
  apply_immediately             = true
  lifecycle {
    ignore_changes = [number_cache_clusters]
  }
}

resource "aws_elasticache_cluster" "api_node_1" {
  cluster_id           = "api-${local.environment}"
  replication_group_id = aws_elasticache_replication_group.api.id
  apply_immediately    = true
}

resource "aws_elasticache_cluster" "api_node_2" {
  cluster_id           = "api-${local.environment}"
  replication_group_id = aws_elasticache_replication_group.api.id
  apply_immediately    = true
}
