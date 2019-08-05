resource "aws_elasticache_subnet_group" "elasticache" {
  name       = "private-${terraform.workspace}"
  subnet_ids = data.aws_subnet.private.*.id
}

