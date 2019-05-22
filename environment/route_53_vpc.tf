resource "aws_route53_zone" "vpc" {
  count   = "${local.vpc_enabled}"
  name    = "${local.vpc_name}.internal"
  comment = ""

  vpc = {
    vpc_id = "${data.aws_vpc.vpc.id}"
  }
}

resource "aws_route53_record" "master-vpc" {
  count   = "${local.vpc_enabled}"
  name    = "master"
  type    = "A"
  zone_id = "${aws_route53_zone.vpc.id}"
  records = ["${aws_instance.master.private_ip}"]
  ttl     = 300
}

resource "aws_route53_record" "jump-vpc" {
  count   = "${local.vpc_enabled}"
  name    = "jump"
  type    = "A"
  zone_id = "${aws_route53_zone.vpc.id}"
  records = ["${aws_instance.jump.private_ip}"]
  ttl     = 60
}

resource "aws_route53_record" "jump-vpc-external" {
  count   = "${local.vpc_enabled}"
  name    = "jump.${local.vpc_name}"
  type    = "A"
  zone_id = "${data.aws_route53_zone.public.id}"
  records = ["${aws_instance.jump.public_ip}"]
  ttl     = 60
}
