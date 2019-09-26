resource "aws_subnet" "private" {
  count             = 3
  cidr_block        = cidrsubnet(aws_vpc.main.cidr_block, 7, count.index + 95)
  availability_zone = data.aws_availability_zones.all.names[count.index]
  vpc_id            = aws_vpc.main.id
  tags = merge(
    local.default_tags,
    { Name = "private" },
  )
}

resource "aws_route_table_association" "private" {
  count          = 3
  route_table_id = aws_route_table.private[count.index].id
  subnet_id      = aws_subnet.private[count.index].id
}

resource "aws_route_table" "private" {
  count  = 3
  vpc_id = aws_vpc.main.id
  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.nat[count.index].id
  }
}

resource "aws_elasticache_subnet_group" "private" {
  name       = local.account.ec_subnet_group
  subnet_ids = aws_subnet.private[*].id
}

resource "aws_db_subnet_group" "private" {
  name       = local.account.db_subnet_group
  subnet_ids = aws_subnet.private[*].id
}
