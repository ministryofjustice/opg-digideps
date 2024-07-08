locals {
  htmltopdf_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ecr_api = local.common_sg_rules.ecr_api
    front = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    },
    admin = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
    sirius_file_sync = {
      port        = 8080
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.sirius_files_sync_service_security_group.id
    }
  }
}

module "htmltopdf_security_group" {
  source      = "./modules/security_group"
  description = "HTML to PDF Service"
  rules       = local.htmltopdf_sg_rules
  name        = "htmltopdf"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
