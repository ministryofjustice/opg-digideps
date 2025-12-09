locals {
  api_rds_sg_rules = {
    api_service = {
      port        = 5432
      type        = "ingress"
      protocol    = "tcp"
      target_type = "security_group_id"
      target      = module.api_service_security_group.id
    }
    ssm_ec2_data_access = {
      port        = 5432
      protocol    = "tcp"
      type        = "ingress"
      target_type = "security_group_id"
      target      = data.aws_security_group.ssm_ec2_data_access.id
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

data "aws_security_group" "ssm_ec2_data_access" {
  name = "data-access-ssm-instance"
}

// Egress rules allowing the SSM instance to connect to the database.
resource "aws_security_group_rule" "postgres_ssm_egress_data_access" {
  description = "data-access-ssm-instance-postgres"
  type        = "egress"
  from_port   = 5432
  to_port     = 5432
  protocol    = "tcp"

  source_security_group_id = module.api_rds_security_group.id
  security_group_id        = data.aws_security_group.ssm_ec2_data_access.id
}
