resource "aws_elasticache_parameter_group" "digideps" {
  name   = "api-cache-params"
  family = "redis6.x"

  parameter {
    name  = "maxmemory-policy"
    value = "allkeys-lru"
  }

}
