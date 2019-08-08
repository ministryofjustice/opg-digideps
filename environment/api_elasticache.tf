resource "aws_security_group" "api_cache" {
  description = "api ec access"
  vpc_id      = data.aws_vpc.vpc.id

  tags = merge(
    local.default_tags,
    {
      "Name" = "api-cache"
    },
  )
}

resource "aws_security_group_rule" "api_cache" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 6379
  to_port                  = 6379
  security_group_id        = aws_security_group.api_cache.id
  source_security_group_id = aws_security_group.api_rds.id
}

resource "aws_security_group_rule" "api_cache_task_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 6379
  to_port                  = 6379
  security_group_id        = aws_security_group.api_cache.id
  source_security_group_id = aws_security_group.api_task.id
}

# TODO: switch to data source subnet group
resource "aws_elasticache_cluster" "api" {
  cluster_id           = "api-${lower(terraform.workspace)}"
  engine               = "redis"
  node_type            = "cache.t2.small"
  num_cache_nodes      = 1
  parameter_group_name = "default.redis5.0"
  engine_version       = "5.0.0"
  port                 = 6379
  subnet_group_name    = local.ec_subnet_group

  security_group_ids = [aws_security_group.api_cache.id]

  tags = {
    InstanceName = "api-${terraform.workspace}"
    Stack        = terraform.workspace
  }
}

resource "aws_route53_record" "api_redis" {
  name    = "api-redis"
  type    = "CNAME"
  zone_id = aws_route53_zone.internal.id
  records = [aws_elasticache_cluster.api.cache_nodes[0].address]
  ttl     = 300
}

