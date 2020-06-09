locals {
  api_service_sg_rules = {
    ecr  = local.common_sg_rules.ecr
    logs = local.common_sg_rules.logs
    s3   = local.common_sg_rules.s3
    cache = {
      port        = 6379
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_cache_security_group.id
    }
    rds = {
      port        = 5432
      type        = "egress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
    admin = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.admin_service_security_group.id
    }
    front = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.front_service_security_group.id
    }
    document_sync = {
      port        = 443
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.document_sync_service_security_group.id
    }
  }
}

module "api_service_security_group" {
  source = "./security_group"
  rules  = local.api_service_sg_rules
  name   = "api-service"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  api_cache_sg_rules = {
    api_service = {
      port        = 6379
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }

    api_unit_test = {
      port        = 6379
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_unit_test.security_group_id
    }
  }
}

module "api_cache_security_group" {
  source = "./security_group"
  rules  = local.api_cache_sg_rules
  name   = "api-cache"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

locals {
  api_rds_sg_rules = {
    api_service = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
    cloud9 = {
      port        = 5432
      protocol    = "tcp"
      type        = "ingress"
      target_type = "security_group_id"
      target      = data.aws_security_group.cloud9.id
    }
    backup = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.backup.security_group_id
    }
    restore = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.restore.security_group_id
    }
    restore_from_production = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.restore_from_production.security_group_id
    }
    api_unit_test = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_unit_test.security_group_id
    }
    integration_test = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.integration_test.security_group_id
    }
    reset_database = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.reset_database.security_group_id
    }
    monitoring_lambda = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.monitoring_lambda_security_group.id
    }
  }
}

module "api_rds_security_group" {
  source = "./security_group"
  rules  = local.api_rds_sg_rules
  name   = "api-rds"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}

data "aws_security_group" "cloud9" {
  filter {
    name   = "tag:aws:cloud9:environment"
    values = [data.terraform_remote_state.shared.outputs.cloud9_env_id]
  }
}
