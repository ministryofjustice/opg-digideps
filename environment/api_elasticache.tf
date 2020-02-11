resource "aws_elasticache_replication_group" "api" {
  count                         = local.account.is_production == 1 ? 1 : 0
  automatic_failover_enabled    = true
  engine                        = "redis"
  engine_version                = "5.0.0"
  availability_zones            = ["eu-west-1a", "eu-west-1b"]
  replication_group_id          = "api-rep-group-${local.environment}"
  replication_group_description = "Replication Group for API"
  node_type                     = "cache.t2.small"
  number_cache_clusters         = 2
  parameter_group_name          = "default.redis5.0"
  port                          = 6379
  subnet_group_name             = local.account.ec_subnet_group
  security_group_ids            = [module.admin_cache_security_group.id]
  tags                          = local.default_tags
  apply_immediately             = true
  tags = {
    InstanceName = "api-${local.environment}"
    Stack        = local.environment
  }
}

resource "aws_elasticache_cluster" "api" {
  count                = local.account.is_production == 1 ? 0 : 1
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
