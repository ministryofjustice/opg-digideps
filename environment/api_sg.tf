locals {
  api_sg_rules = merge(
    local.common_sg_rules_new,
    {
      cache = {
        port        = 6379
        type        = "egress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = aws_security_group.api_cache.id
      }
      rds = {
        port        = 5432
        type        = "egress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = aws_security_group.api_rds.id
      }
      admin = {
        port        = 443
        type        = "ingress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = module.admin_security_group.id
      }
      front = {
        port        = 443
        type        = "ingress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = module.front_security_group.id
      }
    }
  )
}

module "api_security_group" {
  source = "./security_group"
  rules  = local.api_sg_rules
  name   = aws_ecs_task_definition.api.family
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
