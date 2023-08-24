locals {
  admin_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.frontend_cache_security_group.id
    }
    pdf = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.htmltopdf_security_group.id
    }
    api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
    cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.frontend_cache_security_group.id
    }
    admin_elb = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_elb_security_group.id
    }
    notify = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "cidr_block"
      target      = "0.0.0.0/0"
    }
    mock_sirius_integration = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.mock_sirius_integration_security_group.id
    }
  }
}

module "admin_service_security_group" {
  source      = "./modules/security_group"
  description = "Admin Service"
  rules       = local.admin_sg_rules
  name        = "admin-service"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
}

locals {
  admin_elb_sg_rules = {
    admin_service = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
  }
}

module "admin_elb_security_group" {
  source      = "./modules/security_group"
  description = "Admin Elastic Load Balancer"
  rules       = local.admin_elb_sg_rules
  name        = "admin-elb"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
}

# Using a resource rather than module here due to a large list of IPs
resource "aws_security_group_rule" "admin_whitelist" {
  type              = "ingress"
  description       = "whitelist to admin"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.admin_elb_security_group.id
  cidr_blocks       = local.admin_allow_list
}

//No room for rules left in admin_elb_security_group
module "admin_elb_security_group_route53_hc" {
  source      = "./modules/security_group"
  description = "Admin Elastic Load Balancer Healthcheck"
  rules       = local.admin_elb_sg_rules
  name        = "admin-alb-route53-hc"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
}

resource "aws_security_group_rule" "admin_elb_route53_hc_in" {
  type              = "ingress"
  description       = "healthcheck to admin"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.admin_elb_security_group_route53_hc.id
  cidr_blocks       = local.route53_healthchecker_ips
}
