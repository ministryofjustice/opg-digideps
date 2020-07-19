locals {
  wkhtmltopdf_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    front = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    },
    admin = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
    checklist = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.checklist_sync_service_security_group.id
    }
  }
}

module "wkhtmltopdf_security_group" {
  source = "./security_group"
  rules  = local.wkhtmltopdf_sg_rules
  name   = "wkhtmltopdf"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
