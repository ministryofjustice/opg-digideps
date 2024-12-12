locals {
  api_service_sg_rules = {
    ecr            = local.common_sg_rules.ecr
    logs           = local.common_sg_rules.logs
    s3             = local.common_sg_rules.s3
    ssm            = local.common_sg_rules.ssm
    ecr_api        = local.common_sg_rules.ecr_api
    secrets_egress = local.common_sg_rules.secrets
    api_cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.cache_api_sg.id
    }
    rds = {
      port        = 5432
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
    admin = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
    front = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
    sirius_file_sync = {
      port        = 80
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.sirius_files_sync_service_security_group.id
    }
  }
}

module "api_service_security_group" {
  source      = "./modules/security_group"
  description = "API Service"
  rules       = local.api_service_sg_rules
  name        = "api-service"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}

data "aws_security_group" "cloud9" {
  filter {
    name   = "tag:aws:cloud9:environment"
    values = [data.terraform_remote_state.shared.outputs.cloud9_env_id]
  }
}
