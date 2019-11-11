locals {
  wkhtmltopdf_sg_rules = merge(
    local.common_sg_rules_new,
    {
      front = {
        port        = 80
        type        = "ingress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = module.front_security_group.id
      },
      admin = {
        port        = 80
        type        = "ingress"
        protocol    = "tcp"
        target_type = "security_group_id"
        target      = module.admin_security_group.id
      }
    }
  )
}

module "wkhtmltopdf_security_group" {
  source = "./security_group"
  rules  = local.wkhtmltopdf_sg_rules
  name   = aws_ecs_task_definition.wkhtmltopdf.family
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
