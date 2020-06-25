resource "aws_lb" "front" {
  name               = "front-${local.environment}"
  internal           = false
  load_balancer_type = "application"
  subnets            = data.aws_subnet.public.*.id
  idle_timeout       = 300


  security_groups = [module.front_elb_security_group.id, module.front_elb_security_group_route53_hc.id]

  tags = merge(local.default_tags, { "Name" = "front-${local.environment}" }, )
}

resource "aws_lb_listener" "front_https" {
  load_balancer_arn = aws_lb.front.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-Ext-2018-06"
  certificate_arn   = aws_acm_certificate_validation.wildcard.certificate_arn

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
      message_body = file("${path.module}/maintenance/maintenance.html")
      status_code  = "503"
    }
  }

  condition {
    path_pattern {
      values = ["/dd-maintenance"]
    }
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
