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
    db_access_tasks = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.db_access_task_security_group.id
    }
    integration_test = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.integration_tests.security_group_id
    }
    custom_sql_lambda = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = data.aws_security_group.lambda_custom_sql.id
    }
  }
}

module "api_rds_security_group" {
  source      = "./modules/security_group"
  description = "RDS Database"
  rules       = local.api_rds_sg_rules
  name        = "api-rds"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
