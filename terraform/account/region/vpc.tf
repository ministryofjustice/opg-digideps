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
