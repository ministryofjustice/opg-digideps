# TODO: add name_prefix
resource "aws_security_group" "front_cache" {
  description = "front ec access"
  vpc_id      = data.aws_vpc.vpc.id
  tags        = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "front_cache_task_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 6379
  to_port                  = 6379
  security_group_id        = aws_security_group.front_cache.id
  source_security_group_id = aws_security_group.front.id
}

# TODO: switch to data source subnet group
resource "aws_elasticache_cluster" "front" {
  cluster_id           = "front-${lower(terraform.workspace)}"
  engine               = "redis"
  node_type            = "cache.t2.small"
  num_cache_nodes      = 1
  parameter_group_name = "default.redis5.0"
  engine_version       = "5.0.0"
  port                 = 6379
  subnet_group_name    = local.ec_subnet_group
  security_group_ids   = [aws_security_group.front_cache.id]
  tags                 = local.default_tags
}

