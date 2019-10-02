data "aws_availability_zones" "all" {}

resource "aws_vpc" "main" {
  cidr_block = "10.172.0.0/16"
  tags = merge(
    local.default_tags,
    { Name = "private" },
  )
  enable_dns_hostnames = true
  enable_dns_support = true
}

resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.main.id
}

resource "aws_eip" "nat" {
  vpc   = true
  count = 3
}

resource "aws_nat_gateway" "nat" {
  count         = 3
  allocation_id = aws_eip.nat[count.index].id
  subnet_id     = aws_subnet.public[count.index].id
}
