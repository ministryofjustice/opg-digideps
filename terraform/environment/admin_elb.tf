resource "aws_lb" "admin" {
  name                       = "admin-${local.environment}"
  internal                   = false #tfsec:ignore:aws-elb-alb-not-public - This is public LB
  load_balancer_type         = "application"
  subnets                    = data.aws_subnet.public.*.id
  idle_timeout               = 300
  drop_invalid_header_fields = true

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

resource "aws_lb_listener_certificate" "admin_loadbalancer_service_certificate" {
  listener_arn    = aws_lb_listener.admin.arn
  certificate_arn = data.aws_acm_certificate.service_justice.arn
}

resource "aws_lb_listener_certificate" "admin_loadbalancer_service_admin_certificate" {
  listener_arn    = aws_lb_listener.admin.arn
  certificate_arn = data.aws_acm_certificate.service_justice_admin.arn
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

resource "aws_lb_listener" "admin_http" {
  load_balancer_arn = aws_lb.admin.arn
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