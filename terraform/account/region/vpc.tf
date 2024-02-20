data "aws_availability_zones" "all" {}

data "aws_vpc" "default" {
  default = true
}

resource "aws_vpc" "main" {
  cidr_block           = "10.172.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true
  tags = merge(
    var.default_tags,
    { Name = "private" },
  )
}

resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.main.id
  tags = merge(
    var.default_tags,
    {
      "Name" = "internet-gateway-${var.account.name}"
    },
  )
}

resource "aws_eip" "nat" {
  domain = "vpc"
  count  = 3
  tags = merge(
    var.default_tags,
    {
      "Name" = "nat-gw-eip-${var.account.name}-${count.index}"
    },
  )
}

resource "aws_nat_gateway" "nat" {
  count         = 3
  allocation_id = aws_eip.nat[count.index].id
  subnet_id     = aws_subnet.public[count.index].id
  tags = merge(
    var.default_tags,
    {
      "Name" = "nat-gateway-${var.account.name}-${count.index}"
    },
  )
}
