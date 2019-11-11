locals {
  front_sg_rules = {
    logs = local.common_sg_rules_new.logs,
    cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_cache_security_group.id
    },
    api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    },
    pdf = {
      port        = 80
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.wkhtmltopdf_security_group.id
    },
    scan = {
      port        = 8080
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.scan_security_group.id
    }
  }
}

module "front_service_security_group" {
  source = "./security_group"
  rules  = local.front_sg_rules
  name   = aws_ecs_task_definition.front.family
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  front_cache_sg_rules = {
    front_service = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_cache_security_group.id
    }
  }
}

module "front_cache_security_group" {
  source = "./security_group"
  rules  = local.front_cache_sg_rules
  name   = "front-cache-${local.environment}"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  front_elb_sg_rules = {
    front_service = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
  }
}

module "front_elb_security_group" {
  source = "./security_group"
  rules  = local.front_elb_sg_rules
  name   = "front-elb-${local.environment}"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

# Using resources rather than a module here due to a large list of IPs

resource "aws_security_group_rule" "front_elb_http_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 80
  to_port           = 80
  security_group_id = module.front_elb_security_group.id
  cidr_blocks       = local.front_whitelist
}

resource "aws_security_group_rule" "front_elb_https_in" {
  type              = "ingress"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = module.front_elb_security_group.id
  cidr_blocks       = local.front_whitelist
}
