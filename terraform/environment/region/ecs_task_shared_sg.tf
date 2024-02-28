# Generic shared security group with DB access for tasks that require DB access
locals {
  db_access_task_sg_rules = {
    ecr     = local.common_sg_rules.ecr
    logs    = local.common_sg_rules.logs
    s3      = local.common_sg_rules.s3
    ssm     = local.common_sg_rules.ssm
    ecr_api = local.common_sg_rules.ecr_api
    secrets = local.common_sg_rules.secrets
    rds = {
      port        = 5432
      protocol    = "tcp"
      type        = "egress"
      target_type = "security_group_id"
      target      = module.api_rds_security_group.id
    }
  }
}

module "db_access_task_security_group" {
  source      = "./modules/security_group"
  description = "Task Requiring DB Access"
  rules       = local.db_access_task_sg_rules
  name        = "db-access-task"
  tags        = var.default_tags
  vpc_id      = data.aws_vpc.vpc.id
  environment = local.environment
}
