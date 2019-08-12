resource "aws_elasticache_subnet_group" "elasticache" {
  name       = "private-${local.environment}"
  subnet_ids = data.aws_subnet.private.*.id
}

