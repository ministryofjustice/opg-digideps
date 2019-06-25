data "aws_vpc" "vpc" {
  filter {
    name = "tag:Name"
    values = [join(
      ".",
      compact([local.vpc_name, local.account_name, local.domain_name]),
    )]
  }
}

data "aws_availability_zones" "available" {
}

data "aws_subnet" "private" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name = "tag:Name"

    values = [
      "private-1a.${local.vpc_name}",
      "private-1b.${local.vpc_name}",
      "private-1c.${local.vpc_name}",
    ]
  }
}

data "aws_subnet" "public" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.available.names[count.index]

  filter {
    name = "tag:Name"

    values = [
      "public-1a.${local.vpc_name}",
      "public-1b.${local.vpc_name}",
      "public-1c.${local.vpc_name}",
    ]
  }
}

