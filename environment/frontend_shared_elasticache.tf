resource "aws_elasticache_replication_group" "frontend" {
  automatic_failover_enabled = local.account.elasticache_count == 1 ? false : true
  engine                     = "redis"
  engine_version             = "5.0.0"
  replication_group_id       = "frontend-rep-group-${local.environment}"
  description                = "Replication Group for Front and Admin"
  node_type                  = "cache.t2.micro"
  num_cache_clusters         = local.account.elasticache_count
  parameter_group_name       = "default.redis5.0"
  port                       = 6379
  subnet_group_name          = local.account.ec_subnet_group
  security_group_ids         = [module.frontend_cache_security_group.id]
  tags                       = local.default_tags
  apply_immediately          = true
}

locals {
  front_cache_sg_rules = {
    front_service = {
      port        = 6379
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
    admin_service = {
      port        = 6379
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
  }
}

module "frontend_cache_security_group" {
  source = "./security_group"
  rules  = local.front_cache_sg_rules
  name   = "frontend-cache-${local.environment}"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
