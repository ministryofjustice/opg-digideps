resource "aws_elasticache_parameter_group" "digideps" {
  name   = "api-cache-params"
  family = "redis5.0"

  parameter {
    name  = "maxmemory-policy"
    value = "allkeys-lru"
  }

}
