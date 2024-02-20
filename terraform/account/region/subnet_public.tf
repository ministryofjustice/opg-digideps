resource "aws_subnet" "public" {
  count             = 3
  cidr_block        = cidrsubnet(aws_vpc.main.cidr_block, 7, count.index + 45)
  availability_zone = data.aws_availability_zones.all.names[count.index]
  vpc_id            = aws_vpc.main.id
  tags = merge(
    var.default_tags,
    { Name = "public" },
  )
}

resource "aws_route_table_association" "public" {
  count          = 3
  route_table_id = aws_route_table.public[count.index].id
  subnet_id      = aws_subnet.public[count.index].id
}

resource "aws_route_table" "public" {
  count  = 3
  vpc_id = aws_vpc.main.id
  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.igw.id
  }
  tags = merge(
    var.default_tags,
    { Name = "public" },
  )
}
