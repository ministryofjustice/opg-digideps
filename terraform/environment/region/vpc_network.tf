data "aws_nat_gateway" "nat" {
  subnet_id = element(data.aws_subnet.public[*].id, count.index)
  count     = 3
}

data "aws_vpc" "vpc" {
  filter {
    name   = "tag:Name"
    values = ["private"]
  }
}

data "aws_availability_zones" "available" {
}

data "aws_subnet" "private" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["private*"]
  }
}

data "aws_subnet" "public" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["public*"]
  }
}

# New VPC Subnets:

data "aws_subnet" "application" {
  count             = var.account.use_new_network ? 3 : 0
  vpc_id            = data.aws_vpc.main[0].id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["application-*"]
  }
}

data "aws_subnet" "nat" {
  count             = var.account.use_new_network ? 3 : 0
  vpc_id            = data.aws_vpc.main[0].id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["nat-*"]
  }
}

data "aws_subnet" "load_balancer" {
  count             = var.account.use_new_network ? 3 : 0
  vpc_id            = data.aws_vpc.main[0].id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["public-eu-*"]
  }
}

data "aws_nat_gateway" "nat_gateway" {
  count     = var.account.use_new_network ? 3 : 0
  subnet_id = element(data.aws_subnet.nat[*].id, count.index)
}

data "aws_vpc" "main" {
  count = var.account.use_new_network ? 1 : 0
  filter {
    name   = "tag:Name"
    values = ["Digideps-${var.account.name}-vpc"]
  }
}
