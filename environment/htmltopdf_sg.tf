locals {
  htmltopdf_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ecr_api = local.common_sg_rules.ecr_api
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
    document = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.document_sync_service_security_group.id
    }
  }
}

module "htmltopdf_security_group" {
  source      = "./security_group"
  description = "HTML to PDF Service"
  rules       = local.htmltopdf_sg_rules
  name        = "htmltopdf"
  tags        = local.default_tags
  vpc_id      = data.aws_vpc.vpc.id
}
