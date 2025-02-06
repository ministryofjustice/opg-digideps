# INFO - Redis is shared across environments within an account with one api and one front redis per account

# OLD REDIS - TO BE DELETED AS PART OF DDLS-467

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
  security_group_ids         = [aws_security_group.api_cache_sg.id]
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

resource "aws_security_group" "api_cache_sg" {
  name        = "${var.account.name}-shared-cache-api"
  description = "API Cache"
  vpc_id      = aws_vpc.main.id

  tags = merge(var.default_tags, { Name = "cache-api" })

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
  security_group_ids         = [aws_security_group.front_cache_sg.id]
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

resource "aws_security_group" "front_cache_sg" {
  name        = "${var.account.name}-shared-cache-front"
  vpc_id      = aws_vpc.main.id
  description = "Frontend Cache"
  tags        = merge(var.default_tags, { Name = "cache-front" })

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

# NEW REDIS - DELETE THIS COMMENT AS PART OF DDLS-467

# see comments for ticket ddpb-3661 for extra details on in transit encryption decisions
resource "aws_elasticache_replication_group" "redis_cache_api" {
  automatic_failover_enabled = true
  engine                     = "redis"
  engine_version             = "7.1"
  parameter_group_name       = "default.redis7"
  replication_group_id       = "api-cache-${var.account.name}"
  description                = "Replication Group for Account Wide API Cache"
  node_type                  = "cache.t4g.small"
  num_cache_clusters         = 2
  port                       = 6379
  subnet_group_name          = var.account.ec_subnet_group
  security_group_ids         = [aws_security_group.redis_cache_api_sg.id]
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

resource "aws_security_group" "redis_cache_api_sg" {
  name        = "${var.account.name}-account-cache-api"
  description = "API Cache"
  vpc_id      = aws_vpc.main.id

  tags = merge(var.default_tags, { Name = "cache-api" })

  lifecycle {
    create_before_destroy = true
  }
}
#
## see comments for ticket ddpb-3661 for extra details on in transit encryption decisions
resource "aws_elasticache_replication_group" "redis_cache_front" {
  automatic_failover_enabled = true
  engine                     = "redis"
  engine_version             = "7.1"
  parameter_group_name       = "default.redis7"
  replication_group_id       = "frontend-cache-${var.account.name}"
  description                = "Replication Group for Account Wide Front and Admin Cache"
  node_type                  = "cache.t4g.small"
  num_cache_clusters         = 2
  port                       = 6379
  subnet_group_name          = var.account.ec_subnet_group
  security_group_ids         = [aws_security_group.redis_cache_front_sg.id]
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

resource "aws_security_group" "redis_cache_front_sg" {
  name        = "${var.account.name}-account-cache-front"
  vpc_id      = aws_vpc.main.id
  description = "Frontend Account Cache"
  tags        = merge(var.default_tags, { Name = "cache-front" })

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_elasticache_parameter_group" "custom" {
  name   = "api-cache-params7x"
  family = "redis7"

  parameter {
    name  = "maxmemory-policy"
    value = "allkeys-lru"
  }
}
