module "front_elb_security_group" {
  name        = "front-elb"
  source      = "./security_group"
  rules       = local.front_elb_sg_rules
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  description = "Front Elastic Load Balancer"
}

locals {
  front_elb_sg_rules = {
    front_service_http = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
  }
}

# Using resources rather than a module here due to a large list of IPs
resource "aws_security_group_rule" "front_elb_http_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 80
  to_port           = 80
  security_group_id = module.front_elb_security_group.id
  cidr_blocks       = local.front_allow_list
  description       = "Front allow list to Front LB HTTP"
}

resource "aws_security_group_rule" "front_elb_https_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.front_elb_security_group.id
  cidr_blocks       = local.front_allow_list
  description       = "Front allow list to Front LB Secure HTTPS"
}

//No room for rules left in front_elb_security_group
module "front_elb_security_group_route53_hc" {
  name        = "front-elb-route53-hc"
  source      = "./security_group"
  rules       = local.front_elb_sg_rules
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  description = "Front Elastic Load Balancer Healthcheck"
}

resource "aws_security_group_rule" "front_elb_route53_hc_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.front_elb_security_group_route53_hc.id
  cidr_blocks       = local.route53_healthchecker_ips
  description       = "Route53 Healthcheck to Front LB"
}
