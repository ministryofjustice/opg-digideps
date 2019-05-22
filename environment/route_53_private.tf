resource "aws_route53_zone" "internal" {
  name    = "${terraform.workspace}.internal"
  comment = ""

  vpc = {
    vpc_id = "${data.aws_vpc.vpc.id}"
  }
}

resource "aws_route53_record" "jump" {
  name    = "jump"
  type    = "CNAME"
  zone_id = "${aws_route53_zone.internal.id}"
  records = ["${local.jump_record}"]
  ttl     = 300
}

resource "aws_route53_record" "master" {
  name    = "master"
  type    = "CNAME"
  zone_id = "${aws_route53_zone.internal.id}"
  records = ["${local.master_record}"]
  ttl     = 300
}

resource "aws_route53_record" "salt" {
  name    = "salt"
  type    = "CNAME"
  zone_id = "${aws_route53_zone.internal.id}"
  records = ["${local.salt_record}"]
  ttl     = 300
}
