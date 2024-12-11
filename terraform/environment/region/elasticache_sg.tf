# INFO - Elasticache set up in account folder as we use shared elasticache

# Front Elasticache
data "aws_elasticache_replication_group" "front_cache_cluster" {
  replication_group_id = "frontend-redis-${var.account.name}"
}

data "aws_security_group" "front_cache_sg" {
  name = "${var.account.name}-account-cache-frontend"
}

data "aws_security_group" "cache_front_sg" {
  name = "${var.account.name}-shared-cache-front"
}

resource "aws_security_group_rule" "admin_to_cache" {
  description              = "Admin to to front cache cluster"
  from_port                = 6379
  to_port                  = 6379
  type                     = "ingress"
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.cache_front_sg.id
  source_security_group_id = module.admin_service_security_group.id
}

resource "aws_security_group_rule" "front_to_cache" {
  description              = "Frontend to front cache cluster"
  from_port                = 6379
  to_port                  = 6379
  type                     = "ingress"
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.cache_front_sg.id
  source_security_group_id = module.front_service_security_group.id
}

# API Elasticache

data "aws_elasticache_replication_group" "api_cache_cluster" {
  replication_group_id = "api-redis-${var.account.name}"
}

data "aws_security_group" "api_cache_sg" {
  name = "${var.account.name}-account-cache-api"
}

data "aws_security_group" "cache_api_sg" {
  name = "${var.account.name}-shared-cache-api"
}

resource "aws_security_group_rule" "api_to_cache" {
  description              = "Api to Api cache cluster"
  from_port                = 6379
  to_port                  = 6379
  type                     = "ingress"
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.cache_api_sg.id
  source_security_group_id = module.api_service_security_group.id
}
