locals {
  admin_sg_rules = merge(
    local.common_sg_rules_new,
    {
      pdf = {
        port        = 80
        type        = "egress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = module.wkhtmltopdf_security_group.id
      },
      api = {
        port        = 443
        type        = "egress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = aws_security_group.api_rds.id
      }
      cache = {
        port        = 6379
        type        = "egress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = aws_security_group.admin_cache.id
      }
      admin = {
        port        = 443
        type        = "ingress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = aws_security_group.admin_elb.id
      }
    }
  )
}

module "admin_security_group" {
  source = "./security_group"
  rules  = local.admin_sg_rules
  name   = aws_ecs_task_definition.admin.family
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
