resource "aws_lb_target_group" "admin" {
  name                 = "admin-${local.environment}"
  port                 = 80
  protocol             = "HTTP"
  target_type          = "ip"
  vpc_id               = data.aws_vpc.vpc.id
  deregistration_delay = 0
  tags                 = local.default_tags

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
