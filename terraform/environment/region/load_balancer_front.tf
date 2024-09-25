resource "aws_lb" "front" {
  name                       = "front-${local.environment}"
  internal                   = false #tfsec:ignore:aws-elb-alb-not-public - This is a public LB
  load_balancer_type         = "application"
  subnets                    = data.aws_subnet.public[*].id
  idle_timeout               = 300
  drop_invalid_header_fields = true

  security_groups = [module.front_elb_security_group.id, module.front_elb_security_group_route53_hc.id]

  tags = merge(var.default_tags, { "Name" = "front-${local.environment}" }, )
}

resource "aws_lb_listener" "front_https" {
  load_balancer_arn = aws_lb.front.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-FS-1-2-Res-2020-10"
  certificate_arn   = var.certificate_arn

  default_action {
    target_group_arn = aws_lb_target_group.front.arn
    type             = "forward"
  }
}

data "aws_acm_certificate" "service_justice" {
  domain = "*.digideps.opg.service.justice.gov.uk"
}

resource "aws_lb_listener_certificate" "front_loadbalancer_service_certificate" {
  listener_arn    = aws_lb_listener.front_https.arn
  certificate_arn = data.aws_acm_certificate.service_justice.arn
}

resource "aws_lb_listener_certificate" "front_loadbalancer_cdr_certificate" {
  listener_arn    = aws_lb_listener.front_https.arn
  certificate_arn = var.complete_deputy_report_cert_arn
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

resource "aws_lb_target_group" "front" {
  name                 = "front-tg-${local.environment}"
  port                 = 80
  protocol             = "HTTP"
  target_type          = "ip"
  vpc_id               = data.aws_vpc.vpc.id
  deregistration_delay = 15
  tags                 = var.default_tags

  health_check {
    path                = "/health-check"
    interval            = 30
    timeout             = 10
    unhealthy_threshold = 3
    protocol            = "HTTP"
  }

  lifecycle {
    create_before_destroy = true
  }
}
