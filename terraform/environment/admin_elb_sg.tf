module "admin_elb_security_group" {
  name        = "admin-elb"
  source      = "./modules/security_group"
  rules       = local.admin_elb_sg_rules
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
  description = "Admin Elastic Load Balancer"
}

locals {
  admin_elb_sg_rules = {
    admin_service_http = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
  }
}

# Using a resource rather than module here due to a large list of IPs
resource "aws_security_group_rule" "admin_elb_http_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 80
  to_port           = 80
  security_group_id = module.admin_elb_security_group.id
  cidr_blocks       = local.admin_allow_list
  description       = "Admin allow list to Admin LB HTTPS"
}

resource "aws_security_group_rule" "admin_elb_https_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.admin_elb_security_group.id
  cidr_blocks       = local.admin_allow_list
  description       = "Admin allow list to Admin LB HTTP"
}

//No room for rules left in admin_elb_security_group
module "admin_elb_security_group_route53_hc" {
  name        = "admin-elb-route53-hc"
  source      = "./modules/security_group"
  rules       = local.admin_elb_sg_rules
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
  description = "Admin Elastic Load Balancer Healthcheck"
}

resource "aws_security_group_rule" "admin_elb_route53_hc_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.admin_elb_security_group_route53_hc.id
  cidr_blocks       = local.route53_healthchecker_ips
  description       = "Route53 Healthcheck to Admin LB"
}
