resource "aws_elasticache_replication_group" "api" {
  automatic_failover_enabled = local.account.elasticache_count == 1 ? false : true
  engine                     = "redis"
  engine_version             = "5.0.0"
  replication_group_id       = "api-rep-group-${local.environment}"
  description                = "Replication Group for API"
  node_type                  = "cache.t2.micro"
  num_cache_clusters         = local.account.elasticache_count
  parameter_group_name       = "api-cache-params"
  port                       = 6379
  subnet_group_name          = local.account.ec_subnet_group
  security_group_ids         = [module.api_cache_security_group.id]
  apply_immediately          = true
  tags = merge({
    InstanceName = "api-${local.environment}"
    Stack        = local.environment
  }, local.default_tags)
}

locals {
  api_cache_sg_rules = {
    api_service = {
      port        = 6379
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
  }
}

module "api_cache_security_group" {
  source      = "./security_group"
  description = "API Redis"
  rules       = local.api_cache_sg_rules
  name        = "api-cache"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
}

resource "aws_elasticache_parameter_group" "digideps" {
  name   = "api-cache-params"
  family = "redis5.0"

  parameter {
    name  = "maxmemory-policy"
    value = "allkeys-lru"
  }

}
