#TODO: tidy this up
resource "aws_security_group" "admin_elb" {
  name        = "admin-elb-${terraform.workspace}"
  description = "admin elb access for ${terraform.workspace}"
  vpc_id      = data.aws_vpc.vpc.id

  tags = merge(
    local.default_tags,
    {
      "Name" = "admin-elb-${terraform.workspace}"
    },
  )
}

resource "aws_security_group_rule" "admin_elb_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = aws_security_group.admin_elb.id
  cidr_blocks       = local.admin_whitelist
}

resource "aws_security_group_rule" "admin_elb_out" {
  security_group_id        = aws_security_group.admin_elb.id
  source_security_group_id = aws_security_group.admin.id
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
}

resource "aws_lb" "admin" {
  name               = "admin-${terraform.workspace}"
  internal           = false
  load_balancer_type = "application"
  subnets            = data.aws_subnet.public.*.id

  security_groups = [
    aws_security_group.admin_elb.id,
  ]

  tags = merge(
    local.default_tags,
    {
      "Name" = "admin-${terraform.workspace}"
    },
  )
}

resource "aws_lb_listener" "admin" {
  load_balancer_arn = aws_lb.admin.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-Ext-2018-06"
  certificate_arn   = data.aws_acm_certificate.external.arn

  default_action {
    target_group_arn = aws_lb_target_group.admin.arn
    type             = "forward"
  }
}

resource "aws_lb_listener_rule" "admin_maintenance" {
  listener_arn = aws_lb_listener.admin.arn

  action {
    type = "fixed-response"

    fixed_response {
      content_type = "text/html"
      message_body = file("maintenance/maintenance.html")
      status_code  = "503"
    }
  }

  condition {
    field  = "path-pattern"
    values = ["/maintenance"]
  }
}
