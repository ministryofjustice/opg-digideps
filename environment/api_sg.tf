locals {
  api_service_sg_rules = merge(
    local.common_sg_rules_new,
    {
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
    }
  )
}

module "api_service_security_group" {
  source = "./security_group"
  rules  = local.api_service_sg_rules
  name   = aws_ecs_task_definition.api.family
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
  name   = "api-cache-${local.environment}"
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
  }
}

module "api_rds_security_group" {
  source = "./security_group"
  rules  = local.api_rds_sg_rules
  name   = "api-rds-${local.environment}"
  tags   = local.default_tags
  vpc_id = data.aws_vpc.vpc.id
}
