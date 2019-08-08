data "aws_route53_zone" "public" {
  name = local.account.domain
}

resource "aws_route53_zone" "internal" {
  name    = "${terraform.workspace}.internal"
  comment = ""

  vpc {
    vpc_id = data.aws_vpc.vpc.id
  }
}
