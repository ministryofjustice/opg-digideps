data "aws_security_groups" "cloud9" {
  filter {
    name = "tag-key"
    values = ["aws:cloud9:owner"]
  }
}

resource "aws_security_group_rule" "api_rds_cloud9_in" {
  count = "${length(data.aws_security_groups.cloud9.ids)}"
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 5432
  to_port                  = 5432
  security_group_id = "${aws_security_group.api_rds.id}"
  source_security_group_id = "${element(data.aws_security_groups.cloud9.ids, count.index)}"
}
