# TODO: name_prefix
resource "aws_security_group" "admin_cache" {
  description = "admin ec access"
  vpc_id      = data.aws_vpc.vpc.id
  tags        = local.default_tags
}

resource "aws_security_group_rule" "admin_cache_task_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 6379
  to_port                  = 6379
  security_group_id        = aws_security_group.admin_cache.id
  source_security_group_id = aws_security_group.admin.id
}

# TODO: switch to data source subnet group
resource "aws_elasticache_cluster" "admin" {
  cluster_id           = "admin-${local.environment}"
  engine               = "redis"
  node_type            = "cache.t2.small"
  num_cache_nodes      = 1
  parameter_group_name = "default.redis5.0"
  engine_version       = "5.0.0"
  port                 = 6379
  subnet_group_name    = local.account.ec_subnet_group
  security_group_ids   = [aws_security_group.admin_cache.id]
  tags                 = local.default_tags
}

