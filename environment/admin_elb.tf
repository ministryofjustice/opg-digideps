resource "aws_lb" "admin" {
  name               = "admin-${local.environment}"
  internal           = false
  load_balancer_type = "application"
  subnets            = data.aws_subnet.public.*.id
  idle_timeout       = 300

  security_groups = [module.admin_elb_security_group.id, module.admin_elb_security_group_route53_hc.id]

  tags = merge(local.default_tags, { "Name" = "admin-${local.environment}" }, )
}

resource "aws_lb_listener" "admin" {
  load_balancer_arn = aws_lb.admin.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-FS-1-2-Res-2020-10"
  certificate_arn   = aws_acm_certificate_validation.wildcard.certificate_arn

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
