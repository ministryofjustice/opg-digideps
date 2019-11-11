locals {
  front_sg_rules = {
    logs = local.common_sg_rules_new.logs,
    cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = aws_security_group.front_cache.id
    },
    api = {
      port        = 443
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_security_group.id
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

module "front_security_group" {
  source = "./security_group"
  rules  = local.front_sg_rules
  name   = aws_ecs_task_definition.front.family
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
