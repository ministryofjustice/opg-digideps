data "aws_nat_gateway" "nat" {
  subnet_id = element(data.aws_subnet.public[*].id, count.index)
  count     = 3
}
