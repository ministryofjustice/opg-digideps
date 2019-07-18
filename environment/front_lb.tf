resource "aws_security_group" "front_lb" {
  name_prefix = "front-lb-${terraform.workspace}"
  vpc_id      = data.aws_vpc.vpc.id
  tags        = local.default_tags
}

resource "aws_security_group_rule" "front_lb_http_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 80
  to_port           = 80
  security_group_id = aws_security_group.front_lb.id
  cidr_blocks       = local.front_whitelist
}

resource "aws_security_group_rule" "front_lb_https_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = aws_security_group.front_lb.id
  cidr_blocks       = local.front_whitelist
}

resource "aws_security_group_rule" "front_lb_https_out" {
  type                     = "egress"
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.front_lb.id
  source_security_group_id = aws_security_group.front.id
}

resource "aws_lb" "front" {
  name               = "front-${terraform.workspace}"
  internal           = false
  load_balancer_type = "application"
  subnets            = data.aws_subnet.public.*.id

  security_groups = [
    aws_security_group.front_lb.id,
  ]

  tags = merge(
    local.default_tags,
    {
      "Name" = "front-${terraform.workspace}"
    },
  )
}

resource "aws_lb_listener" "front_https" {
  load_balancer_arn = aws_lb.front.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-Ext-2018-06"
  certificate_arn   = data.aws_acm_certificate.external.arn

  default_action {
    target_group_arn = aws_lb_target_group.front.arn
    type             = "forward"
  }
}

resource "aws_lb_listener_rule" "front_maintenance" {
  listener_arn = aws_lb_listener.front_https.arn

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

resource "aws_lb_listener" "front_http" {
  load_balancer_arn = aws_lb.front.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type = "redirect"

    redirect {
      port        = 443
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}

