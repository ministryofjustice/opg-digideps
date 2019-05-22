# TODO: switch to name prefix
resource "aws_security_group" "front" {
  name        = "front-${terraform.workspace}"
  description = "frontend client access for ${terraform.workspace}"
  vpc_id      = "${data.aws_vpc.vpc.id}"
  tags        = "${local.default_tags}"

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "front_task_in" {
  type                     = "ingress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = "${aws_security_group.front.id}"
  source_security_group_id = "${aws_security_group.front_lb.id}"
}

resource "aws_security_group_rule" "front_task_out" {
  type              = "egress"
  protocol          = "-1"
  from_port         = 0
  to_port           = 0
  security_group_id = "${aws_security_group.front.id}"
  cidr_blocks       = ["0.0.0.0/0"]
}
