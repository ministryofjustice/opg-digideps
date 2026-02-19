resource "aws_elasticache_subnet_group" "application" {
  name       = "application"
  subnet_ids = module.network.data_subnets[*].id
}

resource "aws_db_subnet_group" "data" {
  name       = "data-subnet-group-${var.account.name}"
  subnet_ids = module.network.data_subnets[*].id
  tags = merge(
    var.default_tags,
    { Name = "data-subnet-group-${var.account.name}" },
  )
}
