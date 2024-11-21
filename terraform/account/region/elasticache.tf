# INFO - Redis is shared across environments within an account with one api and one front redis per account

# see comments for ticket ddpb-3661 for extra details on in transit encryption decisions
resource "aws_elasticache_replication_group" "cache_api" {
  automatic_failover_enabled = true
  engine                     = "redis"
  engine_version             = "6.x"
  parameter_group_name       = "api-cache-params"
  replication_group_id       = "api-redis-${var.account.name}"
  description                = "Replication Group for API"
  node_type                  = "cache.t4g.small"
  num_cache_clusters         = 2
  port                       = 6379
  subnet_group_name          = var.account.ec_subnet_group
  security_group_ids         = [aws_security_group.cache_api_sg.id]
  snapshot_retention_limit   = 1
  apply_immediately          = var.account.apply_immediately
  snapshot_window            = "02:00-03:50"
  maintenance_window         = var.account.name == "production" ? "wed:04:00-wed:06:00" : "tue:04:00-tue:06:00"
  at_rest_encryption_enabled = true
  #tfsec:ignore:aws-elasticache-enable-in-transit-encryption - too much of a performance hit. To be re-evaluated.
  transit_encryption_enabled = false
  tags = merge({
    InstanceName = "api-${var.account.name}"
    Stack        = var.account.name
  }, var.default_tags)
}

resource "aws_security_group" "cache_api_sg" {
  name        = "${var.account.name}-account-cache-api"
  vpc_id      = aws_vpc.main.id
  tags        = merge(var.default_tags, { Name = "${var.account.name}-account-cache--api" })
  description = "cache api - ${var.account.name}"

  lifecycle {
    create_before_destroy = true
  }
}

# see comments for ticket ddpb-3661 for extra details on in transit encryption decisions
resource "aws_elasticache_replication_group" "front_api" {
  automatic_failover_enabled = true
  engine                     = "redis"
  engine_version             = "6.x"
  parameter_group_name       = "default.redis6.x"
  replication_group_id       = "frontend-redis-${var.account.name}"
  description                = "Replication Group for Front and Admin"
  node_type                  = "cache.t4g.small"
  num_cache_clusters         = 2
  port                       = 6379
  subnet_group_name          = var.account.ec_subnet_group
  security_group_ids         = [aws_security_group.cache_front_sg.id]
  snapshot_retention_limit   = 1
  apply_immediately          = var.account.apply_immediately
  snapshot_window            = "02:00-03:50"
  maintenance_window         = var.account.name == "production" ? "wed:04:00-wed:06:00" : "tue:04:00-tue:06:00"
  at_rest_encryption_enabled = true
  #tfsec:ignore:aws-elasticache-enable-in-transit-encryption - too much of a performance hit. To be re-evaluated.
  transit_encryption_enabled = false
  tags = merge({
    InstanceName = "front-${var.account.name}"
    Stack        = var.account.name
  }, var.default_tags)
}

resource "aws_security_group" "cache_front_sg" {
  name        = "${var.account.name}-account-cache-frontend"
  vpc_id      = aws_vpc.main.id
  description = "cache front - ${var.account.name}"
  tags        = merge(var.default_tags, { Name = "${var.account.name}-account-cache-frontend" })

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_elasticache_parameter_group" "digideps" {
  name   = "api-cache-params"
  family = "redis6.x"

  parameter {
    name  = "maxmemory-policy"
    value = "allkeys-lru"
  }
}
