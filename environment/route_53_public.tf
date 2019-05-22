data "aws_route53_zone" "public" {
  name = "${local.domain}"
}

resource "aws_route53_record" "jump_external" {
  name    = "jump.${terraform.workspace}"
  type    = "CNAME"
  zone_id = "${data.aws_route53_zone.public.id}"
  records = ["${local.jump_external_record}"]
  ttl     = 3600
}
