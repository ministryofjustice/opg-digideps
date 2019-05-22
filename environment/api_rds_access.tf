resource "aws_security_group_rule" "api_rds_master_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id        = "${aws_security_group.api_rds.id}"
  source_security_group_id = "${data.aws_security_group.salt_master.id}"
}

resource "aws_route53_record" "api_postgres" {
  name    = "postgres"
  type    = "CNAME"
  zone_id = "${aws_route53_zone.internal.id}"
  records = ["${aws_db_instance.api.address}"]
  ttl     = 300
}
