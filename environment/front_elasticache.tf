//resource "aws_elasticache_cluster" "front" {
//  cluster_id           = "front-${local.environment}"
//  engine               = "redis"
//  node_type            = "cache.t2.small"
//  num_cache_nodes      = 1
//  parameter_group_name = "default.redis5.0"
//  engine_version       = "5.0.0"
//  port                 = 6379
//  subnet_group_name    = local.account.ec_subnet_group
//  security_group_ids   = [module.front_cache_security_group.id]
//  tags                 = local.default_tags
//}

resource "aws_elasticache_replication_group" "front" {
  automatic_failover_enabled    = true
  engine                        = "redis"
  engine_version                = "5.0.0"
  availability_zones            = ["eu-west-1a", "eu-west-1b"]
  replication_group_id          = "front-rep-group-1"
  replication_group_description = "test description"
  node_type                     = "cache.t2.small"
  number_cache_clusters         = 2
  parameter_group_name          = "default.redis5.0"
  port                          = 6379
  subnet_group_name             = local.account.ec_subnet_group
  security_group_ids            = [module.front_cache_security_group.id]
  tags                          = local.default_tags
  apply_immediately             = true
  lifecycle {
    ignore_changes = [number_cache_clusters]
  }
}

resource "aws_elasticache_cluster" "node1" {
  cluster_id           = "test-redis-001"
  replication_group_id = aws_elasticache_replication_group.front.id
  apply_immediately    = true
}

resource "aws_elasticache_cluster" "node2" {
  cluster_id           = "test-redis-002"
  replication_group_id = aws_elasticache_replication_group.front.id
  apply_immediately    = true
}
