data "aws_availability_zones" "all" {}

resource "aws_vpc" "main" {
  cidr_block           = "10.172.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true
  tags = merge(
    local.default_tags,
    { Name = "private" },
  )
}

resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.main.id
  tags = merge(
    local.default_tags,
    {
      "Name" = "internet-gateway-${local.account.name}"
    },
  )
}

resource "aws_eip" "nat" {
  vpc   = true
  count = 3
  tags = merge(
    local.default_tags,
    {
      "Name" = "nat-gw-eip-${local.account.name}-${count.index}"
    },
  )
}

resource "aws_nat_gateway" "nat" {
  count         = 3
  allocation_id = aws_eip.nat[count.index].id
  subnet_id     = aws_subnet.public[count.index].id
  tags = merge(
    local.default_tags,
    {
      "Name" = "nat-gateway-${local.account.name}-${count.index}"
    },
  )
}
